<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class ProcessBulkProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Thời gian tối đa Job được phép chạy (10 phút)
     */
    public $timeout = 600;

    protected $importJob;

    public function __construct(ImportJob $importJob)
    {
        $this->importJob = $importJob;
    }

    public function handle(): void
    {
        $this->importJob->update(['status' => ImportJob::STATUS_PROCESSING]);

        $relativePath = $this->importJob->file_path;

        if (!Storage::disk('local')->exists($relativePath)) {
            $this->importJob->update([
                'status' => ImportJob::STATUS_FAILED,
                'error_message' => "Không tìm thấy file tại hệ thống lưu trữ: " . $relativePath
            ]);
            return;
        }

        $fullPath = Storage::disk('local')->path($relativePath);
        $file = fopen($fullPath, 'r');

        fgetcsv($file); // Bỏ qua dòng Header đầu tiên của file CSV

        $chunkSize = 50; // Nhóm 500 dòng để insert 1 lần (tối ưu hiệu năng)
        $batchData = [];
        $processedCount = 0;

        try {
            DB::beginTransaction();

            while (($row = fgetcsv($file)) !== false) {
                if (count($row) < 9) continue;

                $profitMargin = (float) $row[5];

                $batchData[] = [
                    'code'                => $row[0],
                    'name'                => $row[1],
                    'category_id'         => $row[2],
                    'brand_id'            => $row[3],
                    'unit'                => !empty($row[4]) ? $row[4] : 'Chiếc', // Mặc định là 'Chiếc' nếu để trống
                    'import_price'        => 0,
                    'profit_margin'       => $profitMargin,
                    'selling_price'       => 0,
                    'low_stock_threshold' => !empty($row[6]) ? (int) $row[6] : 5, // Mặc định báo kho ở mức 5
                    'status'              => 'visible', // Mặc định hiển thị
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                $processedCount++;

                // Khi đủ 50 dòng, thực hiện Bulk Insert
                if (count($batchData) === $chunkSize) {
                    Product::insert($batchData);
                    $batchData = [];

                    $this->importJob->update(['processed_rows' => $processedCount]);
                }
            }

            // Insert nốt những dòng còn dư cuối cùng
            if (!empty($batchData)) {
                Product::insert($batchData);
                $this->importJob->update(['processed_rows' => $processedCount]);
            }

            DB::commit();
            fclose($file);

            // Kích hoạt xóa Cache Storefront để cập nhật hàng mới
            Cache::increment('storefront:products:version');

            // Đánh dấu hoàn thành
            $this->importJob->update(['status' => ImportJob::STATUS_COMPLETED]);
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($file)) fclose($file);

            $this->importJob->update([
                'status' => ImportJob::STATUS_FAILED,
                'error_message' => 'Lỗi dòng ' . $processedCount . ': ' . $e->getMessage()
            ]);
        }
    }
}
