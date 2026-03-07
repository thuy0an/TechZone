<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\Interfaces\AdminAuthRepositoryInterface;

class AdminAuthRepository extends BaseRepository implements AdminAuthRepositoryInterface
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    public function getAdminByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }
}
