<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // 👇 1. Nhớ import cái này

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id'); 

        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',

            'name'        => 'required|string|max:255',
            
            'code'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($productId),
            ],
            
            'description' => 'nullable|string',
            
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