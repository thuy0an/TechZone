<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_addresses')->insert([
            ['user_id' => 1, 'receiver_name' => 'Lê Minh Khách',         'receiver_phone' => '0901111111', 'address' => '12 Lê Lợi, Q1, TP.HCM',       'is_default' => true,  'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'receiver_name' => 'Trần Thị Lan',          'receiver_phone' => '0902222222', 'address' => '45 Nguyễn Trãi, Q5, TP.HCM',  'is_default' => true,  'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'receiver_name' => 'Phạm Quốc Huy',         'receiver_phone' => '0903333333', 'address' => '78 Hùng Vương, Đà Nẵng',       'is_default' => true,  'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 4, 'receiver_name' => 'Ngô Thanh Hà',          'receiver_phone' => '0904444444', 'address' => '22 Cầu Giấy, Hà Nội',          'is_default' => true,  'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 5, 'receiver_name' => 'Võ Nhật Nam',           'receiver_phone' => '0905555555', 'address' => '90 Lý Thường Kiệt, TP.HCM',   'is_default' => true,  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
