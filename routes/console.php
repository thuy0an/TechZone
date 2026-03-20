<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:recalc-selling-price {--dry-run}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $checked = 0;
    $updated = 0;

    Product::query()
        ->select(['id', 'import_price', 'profit_margin', 'selling_price'])
        ->chunkById(200, function ($products) use (&$checked, &$updated, $dryRun) {
            foreach ($products as $product) {
                $checked++;

                $importPrice = (float) $product->import_price;
                $profitMargin = (float) $product->profit_margin;
                $currentSelling = (float) $product->selling_price;

                $nextSelling = round($importPrice * (1 + ($profitMargin / 100)), 2);
                if (abs(round($currentSelling, 2) - $nextSelling) <= 0.00001) {
                    continue;
                }

                $updated++;

                if ($dryRun) {
                    continue;
                }

                $product->update(['selling_price' => $nextSelling]);

                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'import_note_id' => null,
                    'import_price' => $importPrice,
                    'profit_margin' => $profitMargin,
                    'selling_price' => $nextSelling,
                ]);
            }
        });

    if (!$dryRun && $updated > 0) {
        if (!Cache::has('storefront:products:version')) {
            Cache::put('storefront:products:version', 1);
        } else {
            Cache::increment('storefront:products:version');
        }
    }

    $this->info("Checked: {$checked}. Updated: {$updated}." . ($dryRun ? ' (dry run)' : ''));
})->purpose('Recalculate selling_price for all products.');

Artisan::command('report:daily {date?}', function ($date = null) {
    // Nếu không truyền date, mặc định là hôm nay
    $targetDate = $date ?: now()->format('Y-m-d');

    $report = DB::table('orders')
        ->where('status', 'completed')
        ->whereDate('created_at', $targetDate)
        ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
        ->first();

    $revenue = $report->total_revenue ?? 0;
    $orders  = $report->total_orders ?? 0;

    // Tạo nội dung báo cáo chuyên nghiệp
    $content = "--- BÁO CÁO DOANH THU NGÀY {$targetDate} ---\n";
    $content .= "Thời gian xuất báo cáo: " . now()->toDateTimeString() . "\n";
    $content .= "Tổng số đơn hàng: {$orders}\n";
    $content .= "Tổng doanh thu: " . number_format($revenue) . " VNĐ\n";
    $content .= "------------------------------------------\n";

    $fileName = "reports/revenue_{$targetDate}_" . now()->format('H-i') . ".txt";
    Storage::disk('local')->put($fileName, $content);

    $this->info("Đã xuất báo cáo vào file: storage/app/{$fileName}");

    Log::info("Đã chạy báo cáo tự động cho ngày {$targetDate}");
})->purpose('Tính doanh thu và xuất file báo cáo theo ngày');

// Thiết lập lịch chạy mỗi phút để test
Schedule::command('report:daily')->everyMinute();
// Schedule::command('report:daily')->dailyAt('23:59');
