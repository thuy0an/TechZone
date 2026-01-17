<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PaginationRequest
 * 
 * Form Request với validation cho các params phân trang và lọc
 */
class PaginationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|max:50',
            'sort_order' => 'nullable|in:asc,desc,ASC,DESC',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:0,1',
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Số trang phải là số nguyên',
            'page.min' => 'Số trang phải lớn hơn 0',
            'per_page.integer' => 'Số bản ghi mỗi trang phải là số nguyên',
            'per_page.min' => 'Số bản ghi mỗi trang phải lớn hơn 0',
            'per_page.max' => 'Số bản ghi mỗi trang tối đa là 100',
            'sort_order.in' => 'Thứ tự sắp xếp phải là asc hoặc desc',
            'search.max' => 'Từ khóa tìm kiếm tối đa 255 ký tự',
            'created_to.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ];
    }

    /**
     * Get filters array
     */
    public function getFilters(): array
    {
        return $this->only([
            'search',
            'status',
            'category_id',
            'created_from',
            'created_to'
        ]);
    }

    /**
     * Get sort params
     */
    public function getSortParams(): array
    {
        return [
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_order' => strtolower($this->input('sort_order', 'desc')),
        ];
    }

    /**
     * Get per page
     */
    public function getPerPage(int $default = 15): int
    {
        return (int) $this->input('per_page', $default);
    }
}
