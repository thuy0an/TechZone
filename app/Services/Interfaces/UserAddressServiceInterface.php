<?php

namespace App\Services\Interfaces;

interface UserAddressServiceInterface extends BaseServiceInterface
{
    public function getUserAddresses($userId);
    public function createUserAddress($userId, array $data);
    public function updateUserAddress($userId, $addressId, array $data);
    public function deleteUserAddress($userId, $addressId);
}
