<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController; 
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ----- DÀNH CHO KHÁCH HÀNG (USERS) -----

    public function register(RegisterRequest $request){
        try {
            $result = $this->authService->registerUser($request->validated());
            return $this->successResponse($result, 'Đăng ký thành công', 201);
        } catch (\Exception $e){
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function login(LoginRequest $request){
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                'web'
            );
            return $this->successResponse($result, 'Đăng nhập thành công');
        } catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    // ----- DÀNH CHO NHÂN VIÊN (ADMINS) -----

    public function adminLogin(LoginRequest $request){
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                'admin'
            );
            return $this->successResponse($result, 'Chào mừng quản trị viên quay trở lại');
        } catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    // ----- CHUNG -----
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Đăng xuất thành công');
    }

    public function profile(Request $request)
    {
        return $this->successResponse($request->user());
    }
}