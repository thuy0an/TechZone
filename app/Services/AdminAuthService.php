<?php

namespace App\Services;

use App\Services\Interfaces\AdminAuthServiceInterface;
use App\Repositories\Interfaces\AdminAuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AdminAuthService extends BaseService implements AdminAuthServiceInterface
{
    protected AdminAuthRepositoryInterface $adminRepository;

    public function __construct(AdminAuthRepositoryInterface $adminRepository)
    {
        parent::__construct($adminRepository);
        $this->adminRepository = $adminRepository;
    }

    public function login(array $credentials)
    {
        $admin = $this->adminRepository->getAdminByEmail($credentials['email']);

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            throw new \Exception('Email hoặc mật khẩu không chính xác.');
        }

        if (!$admin->is_active) {
            throw new \Exception('Tài khoản của bạn đã bị vô hiệu hóa.');
        }

        // Tạo token với scope (ability) riêng cho admin để phân biệt với token của user
        $token = $admin->createToken('Admin Token', ['admin'])->plainTextToken;

        return [
            'admin' => $admin,
            'access_token' => $token,
        ];
    }

    public function logout($admin)
    {
        return $admin->currentAccessToken()->delete();
    }
}
