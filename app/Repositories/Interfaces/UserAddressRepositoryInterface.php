<?php

namespace App\Repositories\Interfaces;

interface UserAddressRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserAddresses($userId);
    public function clearDefaultAddress($userId);
}
