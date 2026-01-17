<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ApiResponseTrait
 * 
 * Trait có thể dùng chung cho các Controllers
 * Cung cấp các phương thức chuẩn hóa API response
 */
trait ApiResponseTrait
{
    /**
     * Trả về response thành công
     */
    protected function success($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Trả về response tạo mới thành công
     */
    protected function created($data = null, string $message = 'Tạo mới thành công'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Trả về response với dữ liệu phân trang
     */
    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Thành công'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ], 200);
    }

    /**
     * Trả về response lỗi
     */
    protected function error(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Trả về response không tìm thấy
     */
    protected function notFound(string $message = 'Không tìm thấy'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Trả về response lỗi validation
     */
    protected function validationError($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Trả về response lỗi server
     */
    protected function serverError(string $message = 'Lỗi server'): JsonResponse
    {
        return $this->error($message, 500);
    }
}
