<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promo1Id = DB::table('promotions')->insertGetId([
            'name'                => 'Giảm 10% toàn đơn',
            'code'                => 'SALE10',
            'start_date'          => now(),
            'end_date'            => now()->addDays(30),
            'is_active'           => true,
            'type'                => 'discount_bill',
            'discount_value'      => 10,
            'discount_unit'       => 'percent',
            'min_bill_value'      => 5000000,
            'max_discount_amount' => 1000000,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $promo2Id = DB::table('promotions')->insertGetId([
            'name'                => 'Giảm 500k Laptop',
            'code'                => 'LAPTOP500K',
            'start_date'          => now(),
            'end_date'            => now()->addDays(60),
            'is_active'           => true,
            'type'                => 'discount_by_product',
            'discount_value'      => 500000,
            'discount_unit'       => 'amount',
            'min_bill_value'      => 0,
            'max_discount_amount' => 500000,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        DB::table('promotion_product')->updateOrInsert(
            ['promotion_id' => $promo2Id, 'product_id' => 2],
            ['promotion_id' => $promo2Id, 'product_id' => 2]
        );
    }
}
