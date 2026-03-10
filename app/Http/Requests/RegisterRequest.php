<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'required|string|min:10|max:11|regex:/^[0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'phone.regex'    => 'Số điện thoại chỉ được chứa các chữ số.',
            'phone.min'      => 'Số điện thoại phải có ít nhất 10 số.',
            'phone.max'      => 'Số điện thoại không được vượt quá 11 số.',
            'phone.required' => "Số điện thoại không được để trống"
        ];
    }
}
