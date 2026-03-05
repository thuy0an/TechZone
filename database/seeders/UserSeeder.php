<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        DB::table('users')->insert([
            ['name' => 'Lê Minh Khách',  'email' => 'khach1@gmail.com', 'password' => $password, 'phone' => '0901111111', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Trần Thị Lan',   'email' => 'khach2@gmail.com', 'password' => $password, 'phone' => '0902222222', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Phạm Quốc Huy',  'email' => 'khach3@gmail.com', 'password' => $password, 'phone' => '0903333333', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ngô Thanh Hà',   'email' => 'khach4@gmail.com', 'password' => $password, 'phone' => '0904444444', 'is_locked' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Võ Nhật Nam',    'email' => 'khach5@gmail.com', 'password' => $password, 'phone' => '0905555555', 'is_locked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
