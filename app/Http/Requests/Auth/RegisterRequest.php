<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email',

            // Set min lenght là 3 để tiện phát triển
            // confirmed => phải có trường password_confirmation gửi kèm
            'password' => 'required|string|min:3|confirmed',
            'phone' =>'nullable|string|max:11|unique:users,phone',
        ];
    }
}
