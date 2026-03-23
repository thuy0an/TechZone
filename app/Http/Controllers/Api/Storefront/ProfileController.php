<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
