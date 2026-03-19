<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserByEmail($email);
    public function getAllUsers(int $perPage = 10, ?string $keyword = null);
    public function toggleLock(int $id): bool;
    public function createUser(array $data);
}
