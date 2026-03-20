<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDailyRevenueReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-revenue-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');

        // Truy vấn dữ liệu doanh thu thực tế từ Database
        $report = DB::table('orders')
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->first();

        $revenue = $report->total_revenue ?? 0;
        $orders  = $report->total_orders ?? 0;

        // Ghi kết quả vào log hệ thống để kiểm chứng
        Log::info("--- BÁO CÁO TỰ ĐỘNG NGÀY {$today} ---");
        Log::info("Tổng số đơn hàng thành công: {$orders}");
        Log::info("Tổng doanh thu: " . number_format($revenue) . " VNĐ");

        $this->info("Báo cáo ngày {$today} đã được ghi vào log thành công!");

        return 0;
    }
}
