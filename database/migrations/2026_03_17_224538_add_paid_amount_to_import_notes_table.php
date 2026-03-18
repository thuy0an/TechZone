<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_notes', function (Blueprint $table) {
            // Thêm cột paid_amount, mặc định là 0
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_cost')->comment('Số tiền đã thanh toán cho NCC');
        });
    }

    public function down(): void
    {
        Schema::table('import_notes', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
