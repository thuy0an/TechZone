<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',

            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:products,code',
            'description' => 'nullable|string', // Đã thêm trường này
            
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'specifications' => 'nullable|json', 

            'is_hidden'   => 'boolean', 
            'has_serial'  => 'boolean',

            'stock_quantity'         => 'required|integer|min:0',
            'current_import_price'   => 'required|numeric|min:0',
            'specific_profit_margin' => 'nullable|numeric|min:0|max:100',

        ];
    }
    
    protected function prepareForValidation()
    {
        if ($this->brand_id === 'null') {
            $this->merge(['brand_id' => null]); 
        }
    }
}