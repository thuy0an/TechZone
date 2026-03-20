<?php

namespace App\Services;

use App\Services\Interfaces\ProductServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Cache;


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
            'brand_id' => $request->get('brand_id'),
        ];

        $perPage = $request->get('per_page', 12);
        $page = (int) $request->get('page', 1);

        $cacheKey = $this->buildCacheKey('storefront:products:list', [
            'filters' => $filters,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($filters, $perPage) {
            return $this->repository->getStorefrontProducts($filters, $perPage);
        });
    }

    public function getDetailForStorefront($id)
    {
        $cacheKey = $this->buildCacheKey('storefront:products:detail', [
            'id' => $id,
        ]);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($id) {
            return $this->repository->getVisibleProductById($id);
        });
    }

    public function searchBasicForStorefront($request)
    {
        $keyword = trim((string) $request->get('keyword', ''));
        $perPage = (int) $request->get('per_page', 12);
        $page = (int) $request->get('page', 1);

        $cacheKey = $this->buildCacheKey('storefront:products:search-basic', [
            'keyword' => $keyword,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($keyword, $perPage) {
            return $this->repository->searchStorefrontProductsBasic($keyword, $perPage);
        });
    }

    public function searchAdvancedForStorefront($request)
    {
        $filters = [
            'keyword' => trim((string) $request->get('keyword', '')),
            'category_id' => $request->get('category_id'),
            'brand_id' => $request->get('brand_id'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
        ];

        $perPage = (int) $request->get('per_page', 12);
        $page = (int) $request->get('page', 1);

        $cacheKey = $this->buildCacheKey('storefront:products:search-advanced', [
            'filters' => $filters,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($filters, $perPage) {
            return $this->repository->searchStorefrontProductsAdvanced($filters, $perPage);
        });
    }

    public function getAdminProductsList($request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
            'brand_id'    => $request->input('brand_id'),
            'min_price'   => $request->input('min_price'),
            'max_price'   => $request->input('max_price'),
            'low_stock'   => $request->input('low_stock'),
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

        if (isset($data['image_file'])) {
            $data['image'] = $this->cloudinaryService->upload($data['image_file']);
        }

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

    public function getProductsByCategory(int $categoryId, $request)
    {
        $perPage = (int) $request->get('limit', 10);
        $perPage = max(1, min($perPage, 50));
        $page = (int) $request->get('page', 1);

        $cacheKey = $this->buildCacheKey('storefront:products:category', [
            'category_id' => $categoryId,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($categoryId, $perPage) {
            return $this->repository->getProductsByCategory($categoryId, $perPage);
        });
    }

    public function getProductPriceHistories(int $productId, $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        return $this->repository->getPriceHistories($productId, $perPage);
    }

    private function getCacheTtl(): int
    {
        return 60 * 15;
    }

    private function buildCacheKey(string $prefix, array $params): string
    {
        $normalized = $this->normalizeCacheParams($params);
        return $prefix . ':' . sha1(http_build_query($normalized));
    }

    private function normalizeCacheParams(array $params): array
    {
        $filtered = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        array_walk_recursive($filtered, function (&$value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
        });

        $filtered = $this->sortRecursive($filtered);
        return $filtered;
    }

    private function sortRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sortRecursive($value);
            }
        }

        ksort($data);
        return $data;
    }
}
