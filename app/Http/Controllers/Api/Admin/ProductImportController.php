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
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt|max:2048',
            ]);

            $file = $request->file('file');
            $path = $file->store('imports', 'local');

            $totalRows = count(file(storage_path('app/' . $path))) - 1;

            $importJob = \App\Models\ImportJob::create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'status' => 'pending',
                'total_rows' => $totalRows > 0 ? $totalRows : 0,
                'processed_rows' => 0,
            ]);

            \App\Jobs\ProcessBulkProductImport::dispatch($path, $importJob->id);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing started.',
                'data' => [
                    'job_id' => $importJob->id
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Upload Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Đã xảy ra lỗi hệ thống khi tải file lên. Vui lòng thử lại sau!'
            ], 500);
        }
    }

    public function status($id)
    {
        $job = ImportJob::findOrFail($id);

        $progress = 0;
        if ($job->total_rows > 0) {
            $progress = round(($job->processed_rows / $job->total_rows) * 100);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $job->id,
                'status' => $job->status,
                'progress' => $progress,
                'processed' => $job->processed_rows,
                'total' => $job->total_rows,
                'error_message' => $job->error_message,
                'errors' => $job->errors
            ]
        ]);
    }
}
