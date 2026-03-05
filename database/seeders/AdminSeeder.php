<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        DB::table('admins')->insert([
            ['name' => 'Diệp Thụy An',          'email' => '3122410001@techzone.com', 'password' => $password, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Thái Tuấn',              'email' => '3122410451@techzone.com', 'password' => $password, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nguyễn Tuấn Vũ',         'email' => '3122410483@techzone.com', 'password' => $password, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nguyễn Hoàng Ngọc Phong', 'email' => '3122410310@techzone.com', 'password' => $password, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Admin Hệ Thống',          'email' => 'admin@techzone.com',      'password' => $password, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
