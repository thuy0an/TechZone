<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends BaseApiController
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

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'nullable|string|min:8'
        ]);

        $newPassword = $validated['password'] ?? null;
        $this->userService->resetPassword($id, $newPassword);

        return response()->json(['message' => 'Khởi tạo mật khẩu thành công'], 200);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return $this->successResponse($user, 'Lấy thông tin khách hàng thành công');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return $this->successResponse($user, 'Cập nhật thông tin người dùng thành công');
    }

    public function addresses(int $id): JsonResponse
    {
        $addresses = UserAddress::where('user_id', $id)->orderBy('is_default', 'desc')->get();
        return $this->successResponse($addresses, "Lấy sổ địa chỉ người dùng thành công");
    }
}
