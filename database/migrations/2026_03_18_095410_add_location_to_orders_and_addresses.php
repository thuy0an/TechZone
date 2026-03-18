<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm vào bảng user_addresses
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->string('ward_code')->nullable();
            $table->string('province_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('ward_name')->nullable();
        });

        // Thêm vào bảng orders
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->string('ward_code')->nullable();
            $table->string('province_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('ward_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_and_addresses', function (Blueprint $table) {
            //
        });
    }
};
