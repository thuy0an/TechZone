<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CategoryService extends BaseService implements CategoryServiceInterface
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        parent::__construct($categoryRepository);
        $this->categoryRepository = $categoryRepository;
    }

    public function getAll()
    {
        return Cache::remember($this->getCacheKey(), $this->getCacheTtl(), function () {
            return $this->categoryRepository->getAll();
        });
    }

    protected function beforeCreate(array $data): array
    {
        if ($this->categoryRepository->existsByName($data['name'])) {
            throw ValidationException::withMessages([
                'name' => ['Tên loại sản phẩm đã tồn tại']
            ]);
        }

        return $data;
    }

    protected function beforeUpdate(int $id, array $data): array
    {
        if (isset($data['name']) && 
            $this->categoryRepository->existsByName($data['name'], $id)) {
            throw ValidationException::withMessages([
                'name' => ['Tên loại sản phẩm đã tồn tại']
            ]);
        }

        return $data;
    }

    protected function beforeDelete(int $id): void
    {
        $category = $this->categoryRepository->findByIdOrFail($id);
        
        if ($category->products()->exists()) {
            throw new \Exception(
                'Không thể xóa loại sản phẩm này vì đang có sản phẩm thuộc loại này',
                409
            );
        }
    }

    protected function afterCreate($model): void
    {
        $this->clearCache();
    }

    protected function afterUpdate($model): void
    {
        $this->clearCache();
    }

    protected function afterDelete(int $id): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
    }

    private function getCacheKey(): string
    {
        return 'storefront:categories:all';
    }

    private function getCacheTtl(): int
    {
        return 60 * 15;
    }
}
