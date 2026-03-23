<?php

namespace App\Services;


use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\UserAddress; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property OrderRepositoryInterface $repository
 */
class OrderService extends BaseService implements OrderServiceInterface
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(
        OrderRepositoryInterface $repository,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->cartRepository = $cartRepository;
    }

 public function checkout($userId, array $data) {
    return DB::transaction(function () use ($userId, $data) {
        $address = UserAddress::where('id', $data['user_address_id'])
                              ->where('user_id', $userId)->firstOrFail();

        $cart = $this->cartRepository->getCartByUserId($userId);
        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception("Giỏ hàng của bạn đang trống.");
        }

        $subTotal = 0;
        foreach ($cart->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                throw new \Exception("Sản phẩm {$item->product->name} đã hết hàng hoặc không đủ số lượng.");
            }
            $subTotal += $item->price_at_addition * $item->quantity;
        }

    $shippingFee = 30000;
    $discountAmount = 0;
    $promotionId = $data['promotion_id'] ?? null;

    if ($promotionId) {
        $promotion = \App\Models\Promotion::find($promotionId);
        
        if ($promotion && $promotion->is_active && $subTotal >= $promotion->min_bill_value) {
            
            if ($promotion->discount_type === 'percentage') {
                $calculatedDiscount = ($subTotal * $promotion->discount_value / 100);
                
                $maxDiscount = $promotion->max_discount_amount ?? 1000000; 
                
                $discountAmount = min($calculatedDiscount, $maxDiscount);
            } else {
                $discountAmount = $promotion->discount_value;
            }
        }
    }

    $finalTotal = ($subTotal + $shippingFee) - $discountAmount;

    $finalTotal = max(0, $finalTotal);

            $order = $this->repository->create([
                'user_id'          => $userId,
                'promotion_id'     => $promotionId, 
                'order_code'       => 'TZ' . date('Ymd') . strtoupper(Str::random(6)),
                'total_amount'     => $finalTotal,
                'payment_method'   => $data['payment_method'],
                'note' => $data['note'] ?? null,
                'status'           => 'new',
                'receiver_name'    => $address->receiver_name,
                'receiver_phone'   => $address->receiver_phone,
                'shipping_address' => $address->address,
                'province_id'      => $address->province_id,
                'district_id'      => $address->district_id,
                'ward_code'        => $address->ward_code,
                'province_name'    => $address->province_name,
                'district_name'    => $address->district_name,
                'ward_name'        => $address->ward_name,
            ]);

            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item->product_id,
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->price_at_addition,
                    'discount_applied' => 0,
                ]);
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            $cart->items()->delete();

            if ($data['payment_method'] === 'BANK_TRANSFER') {
                $order->bank_info = [
                    'account_no'   => '123456789',
                    'bank_name'    => 'Vietcombank',
                    'account_name' => 'NGUYEN TUAN VU',
                    'message'      => "Thanh toan don hang {$order->order_code}"
                ];
            } elseif ($data['payment_method'] === 'ONLINE') {
                $order->payment_url = "https://mock-vnpay.vn/pay?order=" . $order->order_code;
            }

            return $order;
        });
    }


   public function getOrderSummary($orderId)
{
    $userId = auth()->id();

    
    return \App\Models\Order::with(['details.product', 'promotion'])
        ->where('id', $orderId)
        ->firstOrFail();
}

    public function getMyOrders($userId)
    {
        return $this->repository->getUserOrders($userId);
    }
}
