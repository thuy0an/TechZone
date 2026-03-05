<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('brand_id')->constrained('brands');
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            $table->integer('initial_quantity')->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->decimal('import_price', 15, 2)->default(0);  // giá nhập bình quân
            $table->double('profit_margin')->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->enum('status', ['visible', 'hidden'])->default('visible');
            $table->integer('low_stock_threshold')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
