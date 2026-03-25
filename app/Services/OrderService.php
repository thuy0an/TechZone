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

        // Lấy danh sách ID sản phẩm và sắp xếp để tránh Deadlock khi lock database
        $productIds = $cart->items->pluck('product_id')->toArray();
        sort($productIds);

        // TRANSACTION: Nếu có lỗi ở bất kỳ bước nào, DB sẽ không lưu gì cả.
        DB::beginTransaction();

        try {
            // 1. KHÓA CÁC SẢN PHẨM TRONG GIỎ HÀNG (Chống Race Condition)
            // Lệnh này sẽ yêu cầu MySQL lock các row sản phẩm này lại cho đến khi commit
            $lockedProducts = \App\Models\Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $cartTotal = 0;
            $discountAmount = 0;

            // KIỂM TRA TỒN KHO DỰA TRÊN DỮ LIỆU ĐÃ KHÓA
            foreach ($cart->items as $item) {
                $product = $lockedProducts->get($item->product_id);

                // Kiểm tra xem sản phẩm có bị xóa hoặc tồn kho có đủ không
                if (!$product || $product->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm {$item->product->name} không đủ số lượng trong kho.");
                }
                $cartTotal += ($item->price_at_addition * $item->quantity);
            }

            // XỬ LÝ KHUYẾN MÃI
            if (!empty($data['promotion_id'])) {
                $promotion = $this->promotionRepository->findActiveById(
                    (int) $data['promotion_id'],
                    \Carbon\Carbon::now()
                );

                if (!$promotion) {
                    throw new \Exception('Mã khuyến mãi không hợp lệ.');
                }

                // THÊM: Load danh sách sản phẩm
                $promotion->load('products');

                // SỬA: Truyền thêm $cart->items
                $promotionResult = $this->calculatePromotionForCart($promotion, $cartTotal, $cart->items);
                $discountAmount = $promotionResult['discount_amount'];
                $cartTotal = $promotionResult['final_total'];
            }

            $addressPayload = $this->resolveShippingAddressPayload($userId, $data);

            // TẠO ĐƠN HÀNG
            $order = $this->repository->create([
                'user_id' => $userId,
                'promotion_id' => $data['promotion_id'] ?? null,
                'order_date' => \Carbon\Carbon::now(),
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

            // TẠO CHI TIẾT ĐƠN HÀNG VÀ TRỪ KHO AN TOÀN
            foreach ($cart->items as $item) {
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price_at_addition, // Lưu lại giá lúc chốt đơn
                    'discount_applied' => 0,
                ]);

                // Trừ tồn kho trực tiếp trên Model đã được lock
                $product = $lockedProducts->get($item->product_id);
                $product->stock_quantity -= $item->quantity;
                $product->save();
            }

            // LÀM SẠCH GIỎ HÀNG
            $cart->items()->delete();

            // LƯU TRANSACTION VÀ MỞ KHÓA DỮ LIỆU
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
            // HỦY TRANSACTION VÀ MỞ KHÓA NẾU CÓ LỖI
            DB::rollBack();
            throw $e;
        }
    }

    public function getMyOrders($userId, array $filters = [], int $perPage = 10)
    {
        return $this->repository->getUserOrders($userId, $filters, $perPage);
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

        $promotion->load('products');

        $promotionResult = $this->calculatePromotionForCart($promotion, $cartTotal, $cart->items);

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

            return [
                'receiver_name' => $address->receiver_name,
                'receiver_phone' => $address->receiver_phone,
                'shipping_address' => $address->address,
                'province_id' => null,
                'district_id' => null,
                'ward_code' => null,
                'province_name' => null,
                'district_name' => null,
                'ward_name' => null,
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

    // Lưu ý: Thêm tham số thứ 3 là $cartItems
    private function calculatePromotionForCart($promotion, float $cartTotal, $cartItems): array
    {
        $minBillValue = (float) $promotion->min_bill_value;
        if ($cartTotal < $minBillValue) {
            throw new \Exception('Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã này');
        }

        $eligibleTotal = $cartTotal; // Mặc định áp dụng trên tổng toàn đơn

        // LOGIC MỚI: KIỂM TRA NẾU LÀ KHUYẾN MÃI THEO SẢN PHẨM
        if ($promotion->type === 'discount_by_product') {
            // Lấy mảng ID các sản phẩm được phép áp dụng mã này
            $promoProductIds = $promotion->products->pluck('id')->toArray();

            $eligibleTotal = 0;
            $hasEligibleProduct = false;

            // Duyệt qua giỏ hàng, chỉ cộng dồn tiền của những sản phẩm nằm trong danh sách KM
            foreach ($cartItems as $item) {
                if (in_array($item->product_id, $promoProductIds)) {
                    $hasEligibleProduct = true;
                    $eligibleTotal += ($item->price_at_addition * $item->quantity);
                }
            }

            // Nếu giỏ hàng không có sản phẩm nào khớp, báo lỗi
            if (!$hasEligibleProduct) {
                throw new \Exception('Giỏ hàng không chứa sản phẩm được áp dụng mã khuyến mãi này.');
            }
        }

        $discountAmount = 0;

        // Tính tiền giảm dựa trên $eligibleTotal (Tiền hợp lệ) thay vì $cartTotal
        if ($promotion->discount_unit === 'percent') {
            $discountAmount = $eligibleTotal * ((float) $promotion->discount_value / 100);
        }

        if ($promotion->discount_unit === 'amount') {
            $discountAmount = (float) $promotion->discount_value;
        }

        // Kiểm tra giới hạn mức giảm tối đa
        if (!empty($promotion->max_discount_amount) && $discountAmount > (float) $promotion->max_discount_amount) {
            $discountAmount = (float) $promotion->max_discount_amount;
        }

        // Đảm bảo số tiền giảm không vượt quá số tiền của các sản phẩm được áp dụng
        $discountAmount = min($discountAmount, $eligibleTotal);

        // Tổng tiền cuối cùng của đơn hàng
        $finalTotal = max(0, $cartTotal - $discountAmount);

        return [
            'discount_amount' => round($discountAmount, 2),
            'final_total' => round($finalTotal, 2),
        ];
    }
}
