<?php

namespace App\Http\Controllers\Api\Storefront\Auth;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->register($request->validated());
            return $this->successResponse($data, 'Đăng ký tài khoản thành công', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->validated());
            return $this->successResponse($data, 'Đăng nhập thành công');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            return $this->successResponse([], 'Đăng xuất thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi đăng xuất', $e->getMessage());
        }
    }
}
