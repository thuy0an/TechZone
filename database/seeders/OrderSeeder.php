<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('orders')->insert([
            [
                'user_id'          => 1,
                'promotion_id'     => null,
                'order_date'       => now(),
                'status'           => 'delivered',
                'shipping_address' => '12 Lê Lợi, Q1',
                'receiver_name'    => 'Lê Minh Khách',
                'receiver_phone'   => '0901111111',
                'payment_method'   => 'cash',
                'total_amount'     => 23000000,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'user_id'          => 2,
                'promotion_id'     => 1,
                'order_date'       => now(),
                'status'           => 'confirmed',
                'shipping_address' => '45 Nguyễn Trãi, Q5',
                'receiver_name'    => 'Trần Thị Lan',
                'receiver_phone'   => '0902222222',
                'payment_method'   => 'online',
                'total_amount'     => 22176000,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'user_id'          => 3,
                'promotion_id'     => 2,
                'order_date'       => now(),
                'status'           => 'new',
                'shipping_address' => '78 Hùng Vương',
                'receiver_name'    => 'Phạm Quốc Huy',
                'receiver_phone'   => '0903333333',
                'payment_method'   => 'bank_transfer',
                'total_amount'     => 35500000,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);

        DB::table('order_details')->insert([
            ['order_id' => 1, 'product_id' => 1, 'quantity' => 1, 'unit_price' => 23000000, 'discount_applied' => 0],
            ['order_id' => 2, 'product_id' => 5, 'quantity' => 1, 'unit_price' => 24640000, 'discount_applied' => 2464000],
            ['order_id' => 3, 'product_id' => 2, 'quantity' => 1, 'unit_price' => 36000000, 'discount_applied' => 500000],
        ]);
    }
}
