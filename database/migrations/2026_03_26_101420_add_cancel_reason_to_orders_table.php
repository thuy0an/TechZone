<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột cancel_reason vào bảng orders.
     * Lưu lý do hủy đơn khi khách hàng tự hủy ở trạng thái "new".
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancel_reason')
                ->nullable()
                ->after('status')
                ->comment('Lý do hủy đơn – khách tự điền khi hủy ở trạng thái new');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });
    }
};
