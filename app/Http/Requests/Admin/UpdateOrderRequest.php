<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
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
                Rule::in(['pending', 'completed', 'rejected'])
            ],
            'admin_notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'rejection_reason' => [
                'required_if:status,rejected',
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'حالة الطلب مطلوبة',
            'status.in' => 'حالة الطلب المحددة غير صالحة',

            'admin_notes.string' => 'ملاحظات الإدارة يجب أن تكون نصاً',
            'admin_notes.max' => 'ملاحظات الإدارة طويلة جداً (حد أقصى 1000 حرف)',

            'rejection_reason.required_if' => 'سبب الرفض مطلوب عند رفض الطلب',
            'rejection_reason.string' => 'سبب الرفض يجب أن يكون نصاً',
            'rejection_reason.max' => 'سبب الرفض طويل جداً (حد أقصى 500 حرف)',
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
            'admin_notes' => 'ملاحظات الإدارة',
            'rejection_reason' => 'سبب الرفض',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure rejection_reason is null if status is not rejected
        if ($this->input('status') !== 'rejected') {
            $this->merge(['rejection_reason' => null]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');

            // Validate status transitions
            $this->validateStatusTransition($validator, $order);
        });
    }

    /**
     * Validate status transition rules.
     */
    private function validateStatusTransition($validator, $order): void
    {
        $currentStatus = $order->status;
        $newStatus = $this->input('status');

        // Define allowed status transitions for simplified system
        $allowedTransitions = [
            'pending' => ['completed', 'rejected'],
            'completed' => [], // No transitions from completed
            'rejected' => ['pending'], // Allow re-opening rejected orders
        ];

        if ($currentStatus !== $newStatus) {
            if (!isset($allowedTransitions[$currentStatus]) ||
                !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                $validator->errors()->add('status',
                    "لا يمكن تغيير حالة الطلب من {$currentStatus} إلى {$newStatus}"
                );
            }
        }
    }


}
