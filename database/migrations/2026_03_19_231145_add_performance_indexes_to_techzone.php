<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng Orders
        Schema::table('orders', function (Blueprint $table) {
            // Composite index (Index ghép) cực kỳ hiệu quả cho báo cáo lọc theo ngày và trạng thái
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
            $table->index('province_name', 'idx_orders_province');
        });

        // 2. Bảng Order Details
        Schema::table('order_details', function (Blueprint $table) {
            $table->index('order_id', 'idx_od_order_id');
            $table->index('product_id', 'idx_od_product_id');
        });

        // 3. Bảng Products
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id', 'idx_products_category');
            $table->index('brand_id', 'idx_products_brand');
            $table->index('status', 'idx_products_status');
            $table->index('code', 'idx_products_code');
        });

        // 4. Bảng Import Notes
        Schema::table('import_notes', function (Blueprint $table) {
            $table->index(['status', 'updated_at'], 'idx_import_notes_status_updated');
            $table->index('supplier_id', 'idx_import_notes_supplier');
        });

        // 5. Bảng Import Note Payments (Phục vụ báo cáo dòng tiền)
        Schema::table('import_note_payments', function (Blueprint $table) {
            $table->index('created_at', 'idx_inp_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status_created');
            $table->dropIndex('idx_orders_province');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('idx_od_order_id');
            $table->dropIndex('idx_od_product_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category');
            $table->dropIndex('idx_products_brand');
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_code');
        });

        Schema::table('import_notes', function (Blueprint $table) {
            $table->dropIndex('idx_import_notes_status_updated');
            $table->dropIndex('idx_import_notes_supplier');
        });

        Schema::table('import_note_payments', function (Blueprint $table) {
            $table->dropIndex('idx_inp_created_at');
        });
    }
};
