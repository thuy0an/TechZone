<?php

namespace App\Services;

use App\Services\Interfaces\UserAddressServiceInterface;
use App\Repositories\Interfaces\UserAddressRepositoryInterface;

/**
 * @property UserAddressRepositoryInterface $repository
 */
class UserAddressService extends BaseService implements UserAddressServiceInterface
{
    public function __construct(UserAddressRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function getUserAddresses($userId)
    {
        return $this->repository->getUserAddresses($userId);
    }

    public function createUserAddress($userId, array $data)
    {
        $data['user_id'] = $userId;

        $existingAddresses = $this->repository->getUserAddresses($userId);

        // Nếu user chưa có địa chỉ nào -> Auto set địa chỉ này làm mặc định
        if ($existingAddresses->isEmpty()) {
            $data['is_default'] = true;
        } else if (isset($data['is_default']) && $data['is_default'] == true) {
            $this->repository->clearDefaultAddress($userId);
        } else {
            $data['is_default'] = false;
        }

        return $this->repository->create($data);
    }
}
