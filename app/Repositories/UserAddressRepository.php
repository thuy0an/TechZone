<?php

namespace App\Repositories;

use App\Models\UserAddress;
use App\Repositories\Interfaces\UserAddressRepositoryInterface;

class UserAddressRepository extends BaseRepository implements UserAddressRepositoryInterface
{
    public function __construct(UserAddress $model)
    {
        parent::__construct($model);
    }

    public function getUserAddresses($userId)
    {
        // Lấy danh sách địa chỉ, đưa địa chỉ mặc định lên đầu
        return $this->model->where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function clearDefaultAddress($userId)
    {
        return $this->model->where('user_id', $userId)
            ->update(['is_default' => false]);
    }
}
