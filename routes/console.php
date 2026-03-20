<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Support\Facades\Http;
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
    $targetDate = $date ?: now()->format('Y-m-d');

    // Logic lấy dữ liệu (Giữ nguyên như cũ)
    $report = DB::table('orders')
        ->where('status', 'completed')
        ->whereDate('created_at', $targetDate)
        ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
        ->first();

    $revenue = number_format($report->total_revenue ?? 0);
    $orders = $report->total_orders ?? 0;

    if ($orders == 0 && $date == null) {
        $this->info("Hôm nay chưa có đơn hàng, không gửi Telegram.");
        // return; // Bỏ comment nếu bạn không muốn nhận tin nhắn khi doanh thu bằng 0
    }

    // Tạo nội dung tin nhắn Telegram có định dạng Markdown
    $message = "📊 *BÁO CÁO DOANH THU TECHZONE*\n";
    $message .= "📅 Ngày: `{$targetDate}`\n";
    $message .= "---------------------------\n";
    $message .= "🛒 Tổng đơn hàng: *{$orders}*\n";
    $message .= "💰 Doanh thu: *{$revenue} VNĐ*\n";
    $message .= "---------------------------\n";
    $message .= "✅ _Hệ thống tự động cập nhật_";

    // 3. Gửi tới Telegram Bot
    $token = env('TELEGRAM_BOT_TOKEN');
    $chatId = env('TELEGRAM_CHAT_ID');

    $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]);

    if ($response->successful()) {
        $this->info("Báo cáo đã được gửi tới Telegram!");
    } else {
        $this->error("Lỗi gửi Telegram: " . $response->body());
    }

    // Vẫn lưu file .txt để dự phòng
    $fileName = "reports/revenue_{$targetDate}.txt";
    Storage::disk('local')->put($fileName, $message);
})->purpose('Gửi báo cáo doanh thu qua Telegram');

// Thiết lập lịch chạy mỗi phút để test
Schedule::command('report:daily')->everyMinute();
// Schedule::command('report:daily')->dailyAt('23:59');
