<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Chạy: php artisan db:seed
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            UserSeeder::class,
            UserAddressSeeder::class,
            ProductSeeder::class,
            PromotionSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
