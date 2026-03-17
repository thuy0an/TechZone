<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_notes', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status')
                ->comment('Ngày xác nhận nhập hàng thực tế');
        });
    }

    public function down(): void
    {
        Schema::table('import_notes', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
