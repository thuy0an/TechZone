<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_note_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_note_id')->constrained('import_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('import_price', 15, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_note_details');
    }
};
