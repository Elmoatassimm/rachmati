<?php

namespace App\Http\Requests;

use App\Models\SubscriptionRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    SubscriptionRequest::STATUS_PENDING,
                    SubscriptionRequest::STATUS_APPROVED,
                    SubscriptionRequest::STATUS_REJECTED,
                ])
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get the validation error messages in Arabic.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'يجب تحديد حالة الطلب',
            'status.in' => 'حالة الطلب المحددة غير صحيحة',
            'admin_notes.max' => 'يجب ألا تتجاوز ملاحظات المدير 1000 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'حالة الطلب',
            'admin_notes' => 'ملاحظات المدير',
        ];
    }
}
