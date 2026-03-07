<?php

namespace App\Services;

use App\Services\Interfaces\ProductServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use App\Services\CloudinaryService;


/**
 * @property ProductRepositoryInterface $repository
 */
class ProductService extends BaseService implements ProductServiceInterface
{
    protected $cloudinaryService;

    public function __construct(ProductRepositoryInterface $repository, CloudinaryService $cloudinaryService)
    {
        parent::__construct($repository);
        $this->cloudinaryService = $cloudinaryService;
    }

    public function getListForStorefront($request)
    {
        $filters = [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
        ];

        $perPage = $request->get('per_page', 12);

        return $this->repository->getStorefrontProducts($filters, $perPage);
    }

    public function getDetailForStorefront($id)
    {
        return $this->repository->getVisibleProductById($id);
    }

    public function getAdminProductsList($request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ];
        $perPage = $request->input('per_page', 15);

        return $this->repository->getAdminProducts($filters, $perPage);
    }

    public function createProductForAdmin(array $data)
    {
        if (isset($data['image_file'])) {
            $data['image'] = $this->cloudinaryService->upload($data['image_file']);
        }

        // Khi mới tạo, chưa nhập kho nên giá nhập = 0, kéo theo giá bán = 0
        $data['selling_price'] = 0;
        return $this->repository->create($data);
    }

    public function updateProductForAdmin(int $id, array $data)
    {
        $product = $this->repository->findById($id);

        // Nếu Admin thay đổi 'Biên độ lợi nhuận' (profit_margin), 
        // hệ thống tự động tính lại giá bán dựa trên giá nhập hiện tại.
        if (isset($data['profit_margin'])) {
            $data['selling_price'] = $product->import_price * (1 + $data['profit_margin'] / 100);
        }

        return $this->repository->update($id, $data);
    }

    public function deleteProductForAdmin($id)
    {
        // 1. Nếu có đơn hàng đang hoạt động (không phải cancelled)
        if ($this->repository->hasActiveOrders($id)) {
            // Chỉ ẩn trạng thái và xóa mềm (Soft Delete)
            $this->repository->softDeleteProduct($id);
            return ['type' => 'soft_delete'];
        }

        // 2. Nếu không có đơn hàng hoặc chỉ có đơn đã hủy -> Xóa cứng (Force Delete)
        $this->repository->forceDelete($id);
        return ['type' => 'force_delete'];
    }
}
