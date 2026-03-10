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

            return $this->successResponse($address,  'Thêm địa chỉ thành công', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Thêm địa chỉ thất bại', $e->getMessage(), 400);
        }
    }
}
