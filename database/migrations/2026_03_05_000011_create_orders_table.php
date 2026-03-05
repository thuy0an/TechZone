<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->dateTime('order_date')->nullable();
            $table->enum('status', ['new', 'confirmed', 'delivered', 'cancelled'])->default('new');
            $table->text('shipping_address');
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone', 20)->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'online']);
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_applied', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
    }
};
