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

        $chunkSize = 500; // Nhóm 500 dòng để insert 1 lần (tối ưu hiệu năng)
        $batchData = [];
        $processedCount = 0;

        try {
            DB::beginTransaction();

            while (($row = fgetcsv($file)) !== false) {
                // Giả định thứ tự cột CSV: code, name, category_id, brand_id, import_price, stock_quantity
                $batchData[] = [
                    'code'                => $row[0],
                    'name'                => $row[1],
                    'category_id'         => $row[2],
                    'brand_id'            => $row[3],
                    'import_price'        => $row[4],
                    'profit_margin'       => 20.0,
                    'selling_price'       => $row[4] * 1.2,
                    'stock_quantity'      => $row[5],
                    'initial_quantity'    => $row[5],
                    'status'              => 'visible',
                    'low_stock_threshold' => 5,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                $processedCount++;

                // Khi đủ 500 dòng, thực hiện Bulk Insert
                if (count($batchData) === $chunkSize) {
                    Product::insert($batchData);
                    $batchData = []; // Reset mảng để nhận đợt tiếp theo

                    // Cập nhật tiến độ để Admin xem Progress Bar
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
