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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Khóa ngoại
        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');

        // Thông tin cơ bản
        $table->string('code')->unique(); // SKU: DELL-V3510
        $table->string('name');
        $table->string('image')->nullable();
        $table->text('description')->nullable();
        
        // Cột JSON quan trọng (Lưu cấu hình: RAM 8GB, CPU i5...)
        $table->json('specifications')->nullable(); 
        
        // Trạng thái
        $table->boolean('is_hidden')->default(false);
        $table->boolean('has_serial')->default(true); // True = Laptop/Đth, False = Chuột/Tai nghe

        // Quản lý kho & giá (Sơ bộ)
        $table->integer('stock_quantity')->default(0);
        $table->decimal('current_import_price', 15, 2)->default(0); // Giá nhập gần nhất
        $table->double('specific_profit_margin')->nullable(); // Lãi riêng cho SP này

        // // Bảo hành
        // $table->integer('warranty_duration')->default(12); // 12 tháng
        // $table->enum('warranty_unit', ['month', 'year'])->default('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
