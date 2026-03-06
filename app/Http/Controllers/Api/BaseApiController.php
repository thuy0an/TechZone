<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseApiController
 * 
 * Base controller cho tất cả API controllers
 * - Chuẩn hóa response format
 * - Exception handling
 */
abstract class BaseApiController extends Controller
{
    // =========================================
    // SUCCESS RESPONSES
    // =========================================

    /**
     * Trả về response thành công
     */
    protected function successResponse($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
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
    protected function createdResponse($data = null, string $message = 'Tạo mới thành công'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Trả về response không có nội dung
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Trả về response với dữ liệu phân trang
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Thành công'): JsonResponse
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

    // =========================================
    // ERROR RESPONSES
    // =========================================

    /**
     * Trả về response lỗi
     */
    protected function errorResponse(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): JsonResponse
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
    protected function notFoundResponse(string $message = 'Không tìm thấy'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Trả về response không có quyền
     */
    protected function forbiddenResponse(string $message = 'Không có quyền truy cập'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Trả về response chưa xác thực
     */
    protected function unauthorizedResponse(string $message = 'Chưa đăng nhập'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Trả về response lỗi validation
     */
    protected function validationErrorResponse($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Trả về response lỗi server
     */
    protected function serverErrorResponse(string $message = 'Lỗi server'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    // =========================================
    // EXCEPTION HANDLER
    // =========================================

    /**
     * Xử lý exception và trả về response phù hợp
     */
    protected function handleException(\Exception $e, string $defaultMessage = 'Có lỗi xảy ra'): JsonResponse
    {
        \Illuminate\Support\Facades\Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

        // ModelNotFoundException
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Không tìm thấy dữ liệu');
        }

        // ValidationException
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        // Default
        return $this->serverErrorResponse(
            config('app.debug') ? $e->getMessage() : $defaultMessage
        );
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Lấy filters từ request
     */
    protected function getFilters(): array
    {
        return request()->only([
            'search',
            'status',
            'category_id',
            'created_from',
            'created_to'
        ]);
    }

    /**
     * Lấy sort params từ request
     */
    protected function getSortParams(): array
    {
        return [
            'sort_by' => request('sort_by', 'created_at'),
            'sort_order' => request('sort_order', 'desc'),
        ];
    }

    /**
     * Lấy per_page từ request
     */
    protected function getPerPage(int $default = 15, int $max = 100): int
    {
        $perPage = (int) request('per_page', $default);
        return min($perPage, $max);
    }
}
