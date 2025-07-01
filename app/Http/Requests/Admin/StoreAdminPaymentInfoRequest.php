<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminPaymentInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ccp_number' => 'nullable|string|max:50|regex:/^[0-9\-\s]+$/',
            'ccp_key' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'nom' => 'nullable|string|max:255',
            'adress' => 'nullable|string|max:1000',
            'baridimob' => 'nullable|string|max:20|regex:/^[0-9\+\-\s]+$/',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'ccp_number.regex' => 'رقم CCP يجب أن يحتوي على أرقام وشرطات ومسافات فقط.',
            'ccp_key.regex' => 'مفتاح CCP يجب أن يحتوي على أرقام فقط.',
            'ccp_number.max' => 'رقم CCP لا يجب أن يتجاوز 50 حرف.',
            'ccp_key.max' => 'مفتاح CCP لا يجب أن يتجاوز 20 حرف.',
            'nom.max' => 'الاسم لا يجب أن يتجاوز 255 حرف.',
            'adress.max' => 'العنوان لا يجب أن يتجاوز 1000 حرف.',
            'baridimob.regex' => 'رقم BaridiMob يجب أن يحتوي على أرقام وعلامات فقط.',
            'baridimob.max' => 'رقم BaridiMob لا يجب أن يتجاوز 20 حرف.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ccp_number' => 'رقم CCP',
            'ccp_key' => 'مفتاح CCP',
            'nom' => 'الاسم',
            'adress' => 'العنوان',
            'baridimob' => 'رقم BaridiMob',
        ];
    }
}
