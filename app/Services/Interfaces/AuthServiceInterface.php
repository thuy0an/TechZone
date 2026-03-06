<?php

namespace App\Services\Interfaces;

interface AuthServiceInterface extends BaseServiceInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function logout($user);
}
