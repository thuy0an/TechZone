<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProcessBulkProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importJobId;

    public function __construct($filePath, $importJobId)
    {
        $this->filePath = $filePath;
        $this->importJobId = $importJobId;
    }

    public function handle()
    {
        $importJob = ImportJob::find($this->importJobId);

        if (!$importJob) {
            Log::error("Import job not found: {$this->importJobId}");
            return;
        }

        $importJob->update(['status' => 'processing']);

        if (!Storage::disk('local')->exists($this->filePath)) {
            $importJob->update([
                'status' => 'failed',
                'error_message' => 'Không tìm thấy file CSV trên hệ thống.'
            ]);
            return;
        }

        $fullPath = Storage::disk('local')->path($this->filePath);
        $handle = fopen($fullPath, 'r');

        $rowNum = 0;
        $processedCount = 0;
        $errorList = []; // Mảng chứa chi tiết lỗi từng dòng

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;

            // Bỏ qua dòng tiêu đề
            if ($rowNum === 1) continue;
            // Bỏ qua dòng trống
            if (empty(array_filter($row))) continue;

            $data = [
                'code'                => $row[0] ?? null,
                'name'                => $row[1] ?? null,
                'category_id'         => $row[2] ?? null,
                'brand_id'            => $row[3] ?? null,
                'unit'                => $row[4] ?? null,
                'profit_margin'       => $row[5] ?? null,
                'low_stock_threshold' => $row[6] ?? null,
            ];

            $validator = Validator::make($data, [
                'code'                => 'required|string|max:50|unique:products,code',
                'name'                => 'required|string|max:255',
                'category_id'         => 'required|integer|exists:categories,id',
                'brand_id'            => 'required|integer|exists:brands,id',
                'unit'                => 'required|string|max:50',
                'profit_margin'       => 'required|numeric|min:0',
                'low_stock_threshold' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                // Ghi log Terminal cho Dev
                $errString = implode("; ", $validator->errors()->all());
                Log::channel('import')->warning("Lỗi Dòng {$rowNum}: {$errString}");

                // Lưu vào danh sách lỗi
                $errorList[] = "Dòng {$rowNum}: {$errString}";

                $processedCount++;
                $importJob->update(['processed_rows' => $processedCount]);
                usleep(50000);
                continue;
            }

            try {
                Product::create([
                    'code'                => $data['code'],
                    'name'                => $data['name'],
                    'category_id'         => $data['category_id'],
                    'brand_id'            => $data['brand_id'],
                    'unit'                => $data['unit'],
                    'profit_margin'       => $data['profit_margin'],
                    'low_stock_threshold' => $data['low_stock_threshold'],
                    'import_price'        => 0,
                    'stock_quantity'      => 0,
                    'status'              => 'visible',
                ]);
            } catch (Throwable $e) {
                Log::channel('import')->error("Exception Dòng {$rowNum}: " . $e->getMessage());
                $errorList[] = "Dòng {$rowNum}: Lỗi hệ thống khi lưu - " . $e->getMessage();
            }

            $processedCount++;

            // if ($processedCount % 10 == 0) {
            //     $importJob->update(['processed_rows' => $processedCount]);
            // }
        }

        fclose($handle);

        // Quyết định trạng thái cuối cùng
        $finalStatus = count($errorList) > 0 ? 'completed_with_errors' : 'completed';

        $importJob->update([
            'status' => $finalStatus,
            'processed_rows' => $processedCount,
            'errors' => $errorList // Lưu mảng lỗi vào DB
        ]);
    }
}
