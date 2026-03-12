<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        return parent::__construct($model);
    }

    public function getStorefrontProducts($filters = [], $perPage = 12)
    {
        $query = $this->model->with(['category', 'brand'])->where('status', 'visible');

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($perPage);
    }

    public function getVisibleProductById($id)
    {
        return $this->model->with(['category', 'brand'])
            ->where('status', 'visible')
            ->findOrFail($id);
    }

    public function getAdminProducts(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->with(['category', 'brand']);

        // Tìm kiếm theo tên hoặc mã
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('selling_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('selling_price', '<=', $filters['max_price']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock'] === 'true') {
            // Lọc những sản phẩm có tồn kho nhỏ hơn hoặc bằng ngưỡng cảnh báo
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Thực hiện xóa mềm
     */
    public function softDeleteProduct(int $id)
    {
        $product = $this->model->findOrFail($id);

        $product->status = 'hidden';

        return $product->save();
    }

    public function hasActiveOrders(int $productId): bool
    {
        $product = $this->model->findOrFail($productId);

        // Kiểm tra xem có chi tiết đơn hàng nào của sản phẩm này
        // mà đơn hàng đó KHÔNG có trạng thái 'cancelled' hay không
        return $this->model->where('id', $productId)
            ->whereHas('orderDetails.order', function ($query) {
                // Chỉ tính các đơn hàng không có trạng thái là cancelled
                $query->where('status', '!=', 'cancelled');
            })
            ->exists();
    }


    /**
     * Thực hiện xóa cứng
     */
    public function forceDelete(int $id)
    {
        $product = $this->model->findOrFail($id);
        return $product->delete();
    }

    /**
     * Lấy sản phẩm theo danh mục (chỉ sản phẩm đang bán) có phân trang
     */
    public function getProductsByCategory(int $categoryId, int $perPage = 10)
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->where('status', 'visible')
            ->paginate($perPage);
    }
}
