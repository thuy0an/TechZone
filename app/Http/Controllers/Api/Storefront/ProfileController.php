<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseApiController
{
    public function myInfo()
    {
        $user = Auth::user();
        return $this->successResponse($user, 'Lấy thông tin thành công');
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $user->update($validated);

        return $this->successResponse($user, 'Cập nhật thông tin thành công');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->errorResponse('Đổi mật khẩu thất bại', 'Mật khẩu hiện tại không đúng.', 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return $this->successResponse(null, 'Đổi mật khẩu thành công');
    }
}
