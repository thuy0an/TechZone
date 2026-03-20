<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkProductImport;
use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends BaseApiController
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt|max:10240']); // Max 10MB

        $path = $request->file('file')->store('imports', 'local');

        $fullPath = Storage::disk('local')->path($path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'Không thể truy cập file sau khi lưu'], 500);
        }

        $totalRows = count(file($fullPath)) - 1;

        $importJob = ImportJob::create([
            'file_path'  => $path,
            'total_rows' => $totalRows,
            'status'     => ImportJob::STATUS_PENDING
        ]);

        // Ném vào Queue
        ProcessBulkProductImport::dispatch($importJob);

        return $this->successResponse([
            'job_id'  => $importJob->id
        ], 'File đang được xử lý ngầm!', 202); // 202 Accepted: Đã tiếp nhận nhưng chưa xử lý xong
    }

    public function status($id)
    {
        $job = ImportJob::findOrFail($id);
        return $this->successResponse([
            'status'         => $job->status,
            'processed'      => $job->processed_rows,
            'total'          => $job->total_rows,
            'progress'       => $job->total_rows > 0 ? round(($job->processed_rows / $job->total_rows) * 100) : 0,
            'error_message'  => $job->error_message
        ], 'Thành công');
    }
}
