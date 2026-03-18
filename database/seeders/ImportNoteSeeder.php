<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportNoteSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('import_note_details')->truncate();
        DB::table('import_notes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('import_notes')->insert([
            ['id' => 1, 'admin_id' => 5, 'supplier_id' => 3, 'import_date' => '2026-03-20 09:30:00', 'status' => 'completed', 'completed_at' => null, 'total_cost' => 200000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-16 18:23:25', 'updated_at' => '2026-03-16 18:25:29'],
            ['id' => 2, 'admin_id' => 5, 'supplier_id' => 3, 'import_date' => '2026-03-20 09:30:00', 'status' => 'completed', 'completed_at' => null, 'total_cost' => 250000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-16 18:27:35', 'updated_at' => '2026-03-16 18:28:02'],
            ['id' => 3, 'admin_id' => 5, 'supplier_id' => 3, 'import_date' => '2026-03-20 09:30:00', 'status' => 'completed', 'completed_at' => null, 'total_cost' => 420000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-16 18:32:13', 'updated_at' => '2026-03-16 18:33:45'],
            ['id' => 4, 'admin_id' => 5, 'supplier_id' => 4, 'import_date' => '2026-03-17 14:05:00', 'status' => 'completed', 'completed_at' => '2026-03-17 00:17:05', 'total_cost' => 265000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-17 00:06:20', 'updated_at' => '2026-03-17 00:17:05'],
            ['id' => 5, 'admin_id' => 5, 'supplier_id' => 4, 'import_date' => '2026-03-18 04:18:00', 'status' => 'completed', 'completed_at' => '2026-03-17 00:19:37', 'total_cost' => 150000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-17 00:18:57', 'updated_at' => '2026-03-17 00:19:37'],
            ['id' => 6, 'admin_id' => 5, 'supplier_id' => 4, 'import_date' => '2026-03-17 07:19:00', 'status' => 'completed', 'completed_at' => '2026-03-17 00:20:19', 'total_cost' => 130000000.00, 'paid_amount' => 0.00, 'created_at' => '2026-03-17 00:20:15', 'updated_at' => '2026-03-17 00:20:19'],
            ['id' => 7, 'admin_id' => 5, 'supplier_id' => 5, 'import_date' => '2026-03-18 06:04:00', 'status' => 'completed', 'completed_at' => '2026-03-17 23:04:55', 'total_cost' => 510000000.00, 'paid_amount' => 190000000.00, 'created_at' => '2026-03-17 23:04:40', 'updated_at' => '2026-03-17 23:42:31'],
        ]);

        DB::table('import_note_details')->insert([
            ['import_note_id' => 1, 'product_id' => 6,  'quantity' => 10, 'import_price' => 20000000.00],
            ['import_note_id' => 2, 'product_id' => 6,  'quantity' => 10, 'import_price' => 25000000.00],
            ['import_note_id' => 3, 'product_id' => 5,  'quantity' => 20, 'import_price' => 21000000.00],
            ['import_note_id' => 4, 'product_id' => 6,  'quantity' => 10, 'import_price' => 22500000.00],
            ['import_note_id' => 4, 'product_id' => 3,  'quantity' => 5,  'import_price' => 8000000.00],
            ['import_note_id' => 5, 'product_id' => 11, 'quantity' => 10, 'import_price' => 15000000.00],
            ['import_note_id' => 6, 'product_id' => 11, 'quantity' => 10, 'import_price' => 13000000.00],
            ['import_note_id' => 7, 'product_id' => 12, 'quantity' => 10, 'import_price' => 17000000.00],
            ['import_note_id' => 7, 'product_id' => 11, 'quantity' => 10, 'import_price' => 14000000.00],
            ['import_note_id' => 7, 'product_id' => 1,  'quantity' => 10, 'import_price' => 20000000.00],
        ]);
    }
}
