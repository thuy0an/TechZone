<?php

namespace App\Services\Interfaces;
use Illuminate\Http\Request;
interface UserServiceInterface
{
    public function getListUsers(Request $request);
    public function createNewUser(array $data);
    public function toggleUserLock(int $id);
    public function resetPassword(int $id, ?string $password = null): bool;
}