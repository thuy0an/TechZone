<?php

namespace App\Services\Interfaces;

interface UserAddressServiceInterface extends BaseServiceInterface
{
    public function getUserAddresses($userId);
    public function createUserAddress($userId, array $data);
}
