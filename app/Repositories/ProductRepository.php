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

        // Lọc theo trạng thái ẩn/hiện
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Thực hiện xóa mềm
     */
    public function softDeleteProduct(int $id)
    {
        $product = $this->model->findOrFail($id);

        // Đồng bộ trạng thái kinh doanh sang 'hidden' trước khi xóa mềm
        $product->status = 'hidden';
        $product->save();

        // Eloquent sẽ tự động điền deleted_at nếu Model sử dụng SoftDeletes
        return $product->delete();
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
        $product = $this->model->withTrashed()->findOrFail($id);
        return $product->forceDelete();
    }
}
