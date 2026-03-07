<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;


class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Kiểm tra name category đã tồn tại
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);
        
        if ($excludeId) { 
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Override getSearchableFields để search theo name
     */
    protected function getSearchableFields(): array
    {
        return ['name'];
    }
}