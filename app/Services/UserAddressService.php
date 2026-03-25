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

    public function updateUserAddress($userId, $addressId, array $data)
    {
        $address = $this->repository->findUserAddress($userId, $addressId);
        if (!$address) {
            throw new \Exception('Không tìm thấy địa chỉ.');
        }

        if (array_key_exists('is_default', $data) && $data['is_default'] == true) {
            $this->repository->clearDefaultAddress($userId);
        } elseif (!array_key_exists('is_default', $data)) {
            unset($data['is_default']);
        }

        $address->update($data);
        return $address->fresh();
    }

    public function deleteUserAddress($userId, $addressId)
    {
        $address = $this->repository->findUserAddress($userId, $addressId);
        if (!$address) {
            throw new \Exception('Không tìm thấy địa chỉ.');
        }

        $wasDefault = (bool) $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $other = $this->repository->getUserAddresses($userId)->first();
            if ($other) {
                $other->update(['is_default' => true]);
            }
        }

        return true;
    }
}
