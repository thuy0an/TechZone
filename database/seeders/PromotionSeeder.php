<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('promotions')->insert([
            [
                'name'               => 'Giảm 10% toàn đơn',
                'start_date'         => now(),
                'end_date'           => now()->addDays(30),
                'is_active'          => true,
                'type'               => 'discount_bill',
                'discount_value'     => 10,
                'discount_unit'      => 'percent',
                'min_bill_value'     => 5000000,
                'max_discount_amount'=> 1000000,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'name'               => 'Giảm 500k Laptop',
                'start_date'         => now(),
                'end_date'           => now()->addDays(60),
                'is_active'          => true,
                'type'               => 'discount_by_product',
                'discount_value'     => 500000,
                'discount_unit'      => 'amount',
                'min_bill_value'     => 0,
                'max_discount_amount'=> 500000,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        // Promotion 2 áp dụng cho sản phẩm Dell XPS 13 (product_id = 2)
        DB::table('promotion_product')->insert([
            ['promotion_id' => 2, 'product_id' => 2],
        ]);
    }
}
