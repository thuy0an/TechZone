<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'receiver_name'  => 'required|string|max:255',
            'receiver_phone' => 'required|string|min:10|max:11',
            'address'        => 'required|string',
            'is_default'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
