<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Điện thoại', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laptop',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tablet',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Smartwatch', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Phụ kiện',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
