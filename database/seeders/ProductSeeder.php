<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // category_id: 1=Điện thoại, 2=Laptop, 3=Tablet, 4=Smartwatch, 5=Phụ kiện
        // brand_id:    1=Apple, 2=Samsung, 3=Dell, 4=Asus, 5=Sony
        DB::table('products')->insert([
            [
                'category_id'       => 1,
                'brand_id'          => 1,
                'code'              => 'IP15',
                'name'              => 'iPhone 15 128GB',
                'image'             => 'iphone15.jpg',
                'description'       => 'Điện thoại Apple',
                'unit'              => 'Chiếc',
                'initial_quantity'  => 20,
                'stock_quantity'    => 20,
                'import_price'      => 20000000,
                'profit_margin'     => 0.15,
                'selling_price'     => 23000000,
                'status'            => 'visible',
                'low_stock_threshold' => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'category_id'       => 2,
                'brand_id'          => 3,
                'code'              => 'XPS13',
                'name'              => 'Dell XPS 13',
                'image'             => 'xps13.jpg',
                'description'       => 'Laptop mỏng nhẹ',
                'unit'              => 'Chiếc',
                'initial_quantity'  => 10,
                'stock_quantity'    => 10,
                'import_price'      => 30000000,
                'profit_margin'     => 0.20,
                'selling_price'     => 36000000,
                'status'            => 'visible',
                'low_stock_threshold' => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'category_id'       => 4,
                'brand_id'          => 1,
                'code'              => 'AW9',
                'name'              => 'Apple Watch Series 9',
                'image'             => 'aw9.jpg',
                'description'       => 'Smartwatch cao cấp',
                'unit'              => 'Chiếc',
                'initial_quantity'  => 15,
                'stock_quantity'    => 15,
                'import_price'      => 8000000,
                'profit_margin'     => 0.15,
                'selling_price'     => 9200000,
                'status'            => 'visible',
                'low_stock_threshold' => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'category_id'       => 5,
                'brand_id'          => 5,
                'code'              => 'SONYWH',
                'name'              => 'Sony WH-1000XM5',
                'image'             => 'sony.jpg',
                'description'       => 'Tai nghe chống ồn',
                'unit'              => 'Cái',
                'initial_quantity'  => 25,
                'stock_quantity'    => 25,
                'import_price'      => 6000000,
                'profit_margin'     => 0.25,
                'selling_price'     => 7500000,
                'status'            => 'visible',
                'low_stock_threshold' => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'category_id'       => 1,
                'brand_id'          => 2,
                'code'              => 'S24',
                'name'              => 'Samsung Galaxy S24',
                'image'             => 's24.jpg',
                'description'       => 'Điện thoại Samsung',
                'unit'              => 'Chiếc',
                'initial_quantity'  => 18,
                'stock_quantity'    => 18,
                'import_price'      => 22000000,
                'profit_margin'     => 0.12,
                'selling_price'     => 24640000,
                'status'            => 'visible',
                'low_stock_threshold' => 5,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
