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
            'province_id'   => 'required|string',
            'province_name'   => 'required|string',
            'district_id'   => 'required|string',
            'district_name'   => 'required|string',
            'ward_code'   => 'required|string',
            'ward_name'   => 'required|string',

        ];
    }

    public function messages(): array
    {
        return [];
    }
}
