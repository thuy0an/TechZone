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
        Schema::create('import_note_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_note_id')->constrained('import_notes')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->comment('Người thực hiện thanh toán');
            $table->decimal('amount', 15, 2)->comment('Số tiền trả đợt này');
            // Bạn có thể thêm cột payment_method (cash, bank) nếu muốn, ở đây ta làm đơn giản trước
            $table->timestamps(); // created_at chính là thời gian thanh toán
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_note_payments');
    }
};
