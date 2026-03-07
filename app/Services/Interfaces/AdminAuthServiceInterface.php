<?php

namespace App\Services\Interfaces;

interface AdminAuthServiceInterface extends BaseServiceInterface
{
    public function login(array $credentials);
    public function logout($admin);
}
