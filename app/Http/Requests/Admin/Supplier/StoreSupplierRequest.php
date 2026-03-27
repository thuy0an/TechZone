<?php

namespace App\Http\Requests\Admin\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:suppliers,name',
            'phone' => ['nullable', 'string', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/'],
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Tên nhà cung cấp này đã tồn tại trong hệ thống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.'
        ];
    }
}
