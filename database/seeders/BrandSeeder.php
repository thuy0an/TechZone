<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('brands')->insert([
            ['name' => 'Apple',   'logo' => 'apple.png',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Samsung', 'logo' => 'samsung.png', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dell',    'logo' => 'dell.png',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Asus',    'logo' => 'asus.png',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sony',    'logo' => 'sony.png',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
