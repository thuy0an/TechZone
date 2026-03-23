<?php
namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends BaseApiController {
    protected $orderService;

    public function __construct(OrderServiceInterface $orderService) {
        $this->orderService = $orderService;
    }

    public function processCheckout(Request $request) {
        try {
            $user = Auth::user();
            $order = $this->orderService->checkout($user->id, $request->all());
            
            return $this->successResponse($order, "Đặt hàng thành công", 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400); 
        }
    }
}