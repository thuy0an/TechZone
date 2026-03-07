<?php

namespace App\Repositories\Interfaces;

interface AdminAuthRepositoryInterface extends BaseRepositoryInterface
{
    public function getAdminByEmail(string $email);
}
