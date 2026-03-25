<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreUserAddressRequest;
use App\Services\Interfaces\UserAddressServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends BaseApiController
{
    protected UserAddressServiceInterface $addressService;

    public function __construct(UserAddressServiceInterface $addressService)
    {
        $this->addressService = $addressService;
    }

    // Xem danh sách sổ địa chỉ
    public function index()
    {
        try {
            $userId = Auth::id();
            $addresses = $this->addressService->getUserAddresses($userId);

            return $this->successResponse($addresses, 'Lấy danh sách địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi lấy danh sách',  $e->getMessage(), 400);
        }
    }

    // Thêm địa chỉ mới
    public function store(StoreUserAddressRequest $request)
    {
        try {
            // Validate nhanh, nếu cần bạn có thể tách ra StoreUserAddressRequest sau
            $validated = $request->validated();

            $userId = Auth::id();
            $address = $this->addressService->createUserAddress($userId, $validated);

            return $this->createdResponse($address,  'Thêm địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Thêm địa chỉ thất bại', $e->getMessage(), 400);
        }
    }

    // Cập nhật địa chỉ
    public function update(StoreUserAddressRequest $request, int $id)
    {
        try {
            $validated = $request->validated();
            $userId = Auth::id();

            $address = $this->addressService->updateUserAddress($userId, $id, $validated);
            return $this->successResponse($address, 'Cập nhật địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Cập nhật địa chỉ thất bại', $e->getMessage(), 400);
        }
    }

    // Xóa địa chỉ
    public function destroy(int $id)
    {
        try {
            $userId = Auth::id();
            $this->addressService->deleteUserAddress($userId, $id);

            return $this->successResponse(null, 'Xóa địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Xóa địa chỉ thất bại', $e->getMessage(), 400);
        }
    }
}
