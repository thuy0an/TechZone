<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Services\Interfaces\AdminAuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    protected AdminAuthServiceInterface $authService;

    public function __construct(AdminAuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->validated());
            return $this->successResponse($data, 'Admin đăng nhập thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Đăng nhập thất bại', 401, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            return $this->successResponse([], 'Admin đăng xuất thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi đăng xuất', $e->getMessage());
        }
    }
}
