<?php

namespace App\Http\Requests\Admin\ImportNote;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportNoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'supplier_id'            => 'required|exists:suppliers,id',
            'import_date'            => 'required|date',
            'details'                => 'required|array|min:1', // Phải có ít nhất 1 sản phẩm
            'details.*.product_id'   => 'required|exists:products,id',
            'details.*.quantity'     => 'required|integer|min:1',
            'details.*.import_price' => 'required|numeric|min:0',
        ];
    }
}
