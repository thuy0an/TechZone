<?php

namespace App\Services;

use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\PromotionRepositoryInterface;
use App\Repositories\Interfaces\UserAddressRepositoryInterface;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property OrderRepositoryInterface $repository
 */
class OrderService extends BaseService implements OrderServiceInterface
{
    protected CartRepositoryInterface $cartRepository;
    protected PromotionRepositoryInterface $promotionRepository;
    protected UserAddressRepositoryInterface $userAddressRepository;

    public function __construct(
        OrderRepositoryInterface $repository,
        CartRepositoryInterface $cartRepository,
        PromotionRepositoryInterface $promotionRepository,
        UserAddressRepositoryInterface $userAddressRepository
    ) {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->cartRepository = $cartRepository;
        $this->promotionRepository = $promotionRepository;
        $this->userAddressRepository = $userAddressRepository;
    }

    public function checkout($userId, array $data)
    {
        // Lấy giỏ hàng của User
        $cart = $this->cartRepository->getCartByUserId($userId);

        if ($cart->items->isEmpty()) {
            throw new \Exception("Giỏ hàng của bạn đang trống.");
        }

        // TRANSACTION: Nếu có lỗi ở bất kỳ bước nào, DB sẽ không lưu gì cả.
        DB::beginTransaction();

        try {
            $cartTotal = 0;
            $discountAmount = 0;

            // Tính tổng tiền & Kiểm tra tồn kho lần cuối
            foreach ($cart->items as $item) {
                $product = $item->product;
                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho.");
                }
                $cartTotal += ($item->price_at_addition * $item->quantity);
            }

            if (!empty($data['promotion_id'])) {
                $promotion = $this->promotionRepository->findActiveById(
                    (int) $data['promotion_id'],
                    Carbon::now()
                );

                if (!$promotion) {
                    throw new \Exception('Mã khuyến mãi không hợp lệ.');
                }

                $promotionResult = $this->calculatePromotionForCart($promotion, $cartTotal);
                $discountAmount = $promotionResult['discount_amount'];
                $cartTotal = $promotionResult['final_total'];
            }

            $addressPayload = $this->resolveShippingAddressPayload($userId, $data);

            // Tạo Đơn hàng (Order)
            $order = $this->repository->create([
                'user_id' => $userId,
                'promotion_id' => $data['promotion_id'] ?? null,
                'order_date' => Carbon::now(),
                'order_code' => $this->generateOrderCode(),
                'status' => 'new',
                'shipping_address' => $addressPayload['shipping_address'],
                'receiver_name' => $addressPayload['receiver_name'],
                'receiver_phone' => $addressPayload['receiver_phone'],
                'payment_method' => $data['payment_method'],
                'total_amount' => $cartTotal,
                'province_id' => $addressPayload['province_id'],
                'district_id' => $addressPayload['district_id'],
                'ward_code' => $addressPayload['ward_code'],
                'province_name' => $addressPayload['province_name'],
                'district_name' => $addressPayload['district_name'],
                'ward_name' => $addressPayload['ward_name'],
            ]);

            // Tạo Chi tiết đơn hàng (OrderDetails) & Trừ Tồn Kho
            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price_at_addition, // Lưu lại giá lúc chốt đơn
                    'discount_applied' => 0,
                ]);

                // Trừ tồn kho
                $product = Product::find($item->product_id);
                $product->decrement('stock_quantity', $item->quantity);
            }

            // Làm sạch giỏ hàng (Xóa các items đã mua)
            $cart->items()->delete();

            // LƯU TRANSACTION
            DB::commit();

            return [
                'order' => $order,
                'payload' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'payment_method' => $order->payment_method,
                    'promotion_id' => $order->promotion_id,
                    'cart_total' => round($cartTotal + $discountAmount, 2),
                    'discount_amount' => round($discountAmount, 2),
                    'final_total' => round($cartTotal, 2),
                    'total_amount' => round($cartTotal, 2),
                ],
            ];
        } catch (\Exception $e) {
            // HỦY TRANSACTION NẾU CÓ LỖI
            DB::rollBack();
            throw $e;
        }
    }

    public function getMyOrders($userId)
    {
        return $this->repository->getUserOrders($userId);
    }

    public function applyPromotion($userId, string $promotionCode): array
    {
        $cart = $this->cartRepository->getCartByUserId($userId);

        if ($cart->items->isEmpty()) {
            throw new \Exception('Giỏ hàng của bạn đang trống.');
        }

        $cartTotal = $this->calculateCartTotal($cart->items);
        $promotion = $this->promotionRepository->findActiveByCode($promotionCode, Carbon::now());

        if (!$promotion) {
            throw new \Exception('Mã khuyến mãi không hợp lệ.');
        }

        $promotionResult = $this->calculatePromotionForCart($promotion, $cartTotal);

        return [
            'promotion_id' => $promotion->id,
            'cart_total' => $cartTotal,
            'discount_amount' => $promotionResult['discount_amount'],
            'final_total' => $promotionResult['final_total'],
        ];
    }

    public function getOrderSummary($userId, $orderId): array
    {
        $order = $this->repository->getUserOrderSummary($userId, $orderId);

        if (!$order) {
            throw new \Exception('forbidden');
        }

        $items = $order->details->map(function ($detail) {
            $product = $detail->product;
            $unitPrice = (float) $detail->unit_price;
            $quantity = (int) $detail->quantity;
            return [
                'product_id' => $detail->product_id,
                'product_name' => $product?->name,
                'product_image' => $product?->image,
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        })->values();

        $summary = [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'total_amount' => round((float) $order->total_amount, 2),
            'promotion_id' => $order->promotion_id,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'province_name' => $order->province_name,
            'district_name' => $order->district_name,
            'ward_name' => $order->ward_name,
            'created_at' => optional($order->created_at)->toDateTimeString(),
            'items' => $items,
        ];

        if ($order->payment_method === 'bank_transfer') {
            $bankInfo = config('payment.bank_transfer');
            $summary['bank_transfer_info'] = [
                'bank_name' => $bankInfo['bank_name'],
                'account_number' => $bankInfo['account_number'],
                'account_owner' => $bankInfo['account_owner'],
                'transfer_note' => 'Thanh toan don hang ' . $order->id,
            ];
        }

        return $summary;
    }

    private function calculateCartTotal($items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $total += ($item->price_at_addition * $item->quantity);
        }

        return round($total, 2);
    }

    private function resolveShippingAddressPayload($userId, array $data): array
    {
        if (!empty($data['user_address_id'])) {
            $address = $this->userAddressRepository->findUserAddress($userId, (int) $data['user_address_id']);

            if (!$address) {
                throw new \Exception('Địa chỉ nhận hàng không tồn tại hoặc không thuộc về bạn.');
            }

            $fullAddressParts = [
                $address->address,
                $address->ward_name,
                $address->district_name,
                $address->province_name,
            ];

            $fullAddress = implode(', ', array_filter($fullAddressParts));

            return [
                'receiver_name' => $address->receiver_name,
                'receiver_phone' => $address->receiver_phone,
                'shipping_address' => $fullAddress ?: $address->address,
                'province_id' => $address->province_id,
                'district_id' => $address->district_id,
                'ward_code' => $address->ward_code,
                'province_name' => $address->province_name,
                'district_name' => $address->district_name,
                'ward_name' => $address->ward_name,
            ];
        }

        return [
            'receiver_name' => $data['receiver_name'],
            'receiver_phone' => $data['receiver_phone'],
            'shipping_address' => $data['shipping_address'],
            'province_id' => $data['province_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'ward_code' => $data['ward_code'] ?? null,
            'province_name' => $data['province_name'] ?? null,
            'district_name' => $data['district_name'] ?? null,
            'ward_name' => $data['ward_name'] ?? null,
        ];
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'ORD' . Carbon::now()->format('YmdHis') . strtoupper(bin2hex(random_bytes(2)));
        } while (DB::table('orders')->where('order_code', $code)->exists());

        return $code;
    }

    private function calculatePromotionForCart($promotion, float $cartTotal): array
    {
        $minBillValue = (float) $promotion->min_bill_value;
        if ($cartTotal < $minBillValue) {
            throw new \Exception('Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã này');
        }

        $discountAmount = 0;
        if ($promotion->discount_unit === 'percent') {
            $discountAmount = $cartTotal * ((float) $promotion->discount_value / 100);
        }

        if ($promotion->discount_unit === 'amount') {
            $discountAmount = (float) $promotion->discount_value;
        }

        if (!empty($promotion->max_discount_amount) && $discountAmount > (float) $promotion->max_discount_amount) {
            $discountAmount = (float) $promotion->max_discount_amount;
        }

        $discountAmount = min($discountAmount, $cartTotal);
        $finalTotal = max(0, $cartTotal - $discountAmount);

        return [
            'discount_amount' => round($discountAmount, 2),
            'final_total' => round($finalTotal, 2),
        ];
    }
}
