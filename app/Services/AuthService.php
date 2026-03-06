<?php

namespace App\Services;

use App\Services\Interfaces\AuthServiceInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthService extends BaseService implements AuthServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        // Mã hóa mật khẩu trước khi lưu
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        // Tạo token
        $token = $user->createToken('Storefront Token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->getUserByEmail($credentials['email']);

        // Kiểm tra email và mật khẩu
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Email hoặc mật khẩu không chính xác.');
        }

        // Kiểm tra tài khoản có bị khóa không
        if ($user->is_locked) {
            throw new \Exception('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.');
        }

        // Tạo token
        $token = $user->createToken('Storefront Token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
        ];
    }

    public function logout($user)
    {
        // Xóa token hiện tại đang dùng
        return $user->currentAccessToken()->delete();
    }
}
