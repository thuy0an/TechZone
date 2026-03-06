<?php

namespace App\Services;

use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
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

    public function __construct(
        OrderRepositoryInterface $repository,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->cartRepository = $cartRepository;
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
            $totalAmount = 0;

            // Tính tổng tiền & Kiểm tra tồn kho lần cuối
            foreach ($cart->items as $item) {
                $product = $item->product;
                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho.");
                }
                $totalAmount += ($item->price_at_addition * $item->quantity);
            }

            // (Sau này trừ discount_applied ở đây nếu có promotion_id)

            // Tạo Đơn hàng (Order)
            $order = $this->repository->create([
                'user_id' => $userId,
                'promotion_id' => $data['promotion_id'] ?? null,
                'order_date' => Carbon::now(),
                'status' => 'new',
                'shipping_address' => $data['shipping_address'],
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'payment_method' => $data['payment_method'],
                'total_amount' => $totalAmount,
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

            return $order;
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
}
