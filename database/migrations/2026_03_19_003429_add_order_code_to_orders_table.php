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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_code', 50)->unique()->after('promotion_id');

            // Nếu bạn muốn thêm cả receiver_email như trong SQL đã bàn
            if (!Schema::hasColumn('orders', 'receiver_email')) {
                $table->string('receiver_email')->nullable()->after('receiver_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_code');
            $table->dropColumn('receiver_email');
        });
    }
};
