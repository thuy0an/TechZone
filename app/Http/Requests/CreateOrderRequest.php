<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $paymentMethod = $this->input('payment_method');
        if (is_string($paymentMethod)) {
            $normalized = strtolower($paymentMethod);
            $map = [
                'cod' => 'cash',
                'bank_transfer' => 'bank_transfer',
                'online' => 'online',
                'cash' => 'cash',
            ];
            if (isset($map[$normalized])) {
                $this->merge(['payment_method' => $map[$normalized]]);
            }
        }
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'user_address_id' => [
                'nullable',
                'integer',
                Rule::exists('user_addresses', 'id')->where('user_id', $userId),
            ],
            'receiver_name' => 'required_without:user_address_id|string|max:255',
            'receiver_phone' => 'required_without:user_address_id|string|max:20',
            'shipping_address' => 'required_without:user_address_id|string',
            'payment_method' => 'required|in:cash,bank_transfer,online',
            'promotion_id' => 'nullable|exists:promotions,id',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'ward_code' => 'nullable|string|max:50',
            'province_name' => 'nullable|string|max:255',
            'district_name' => 'nullable|string|max:255',
            'ward_name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_address_id.integer' => 'Địa chỉ nhận hàng không hợp lệ.',
            'user_address_id.exists' => 'Địa chỉ nhận hàng không tồn tại hoặc không thuộc về bạn.',
            'receiver_name.required_without' => 'Vui lòng nhập tên người nhận.',
            'receiver_phone.required_without' => 'Vui lòng nhập số điện thoại.',
            'shipping_address.required_without' => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
        ];
    }
}
