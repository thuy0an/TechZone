<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Lấy danh sách khách hàng (Admin xem)
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getListUsers($request);
        return response()->json($users, 200);
    }

    /**
     * Tạo tài khoản khách hàng mới kèm mật khẩu tạm
     */
    public function store(Request $request): JsonResponse
    {
        // Validation dữ liệu đầu vào theo nghiệp vụ
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|digits_between:10,11',
            'address'  => 'required|string',
            'password' => 'nullable|min:8' 
        ]);

        $user = $this->userService->createNewUser($validated);
        
        return response()->json([
            'message' => 'Tạo tài khoản khách hàng thành công',
            'data'    => $user
        ], 201);
    }

    /**
     * Khóa hoặc mở khóa tài khoản (Toggle lock)
     */
    public function toggleLock(int $id): JsonResponse
    {
        $result = $this->userService->toggleUserLock($id);
        
        if ($result) {
            return response()->json(['message' => 'Cập nhật trạng thái tài khoản thành công'], 200);
        }

        return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
    }
}