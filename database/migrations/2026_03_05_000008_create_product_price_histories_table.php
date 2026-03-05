<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nhật ký giá - ghi log mọi thay đổi giá theo từng lô nhập
        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('import_note_id')->nullable()->constrained('import_notes')->nullOnDelete();
            $table->decimal('import_price', 15, 2);
            $table->double('profit_margin');
            $table->decimal('selling_price', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
    }
};
