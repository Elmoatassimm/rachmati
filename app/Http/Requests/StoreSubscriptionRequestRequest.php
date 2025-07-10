<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class StoreSubscriptionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isDesigner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pricing_plan_id' => [
                'required',
                'integer',
                'exists:pricing_plans,id'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'payment_proof' => [
                'nullable',
                File::image()
                    ->max(5 * 1024) // 5MB max
                    ->types(['jpeg', 'jpg', 'png', 'gif', 'webp'])
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
            'pricing_plan_id.required' => 'يجب اختيار خطة اشتراك',
            'pricing_plan_id.exists' => 'خطة الاشتراك المحددة غير صحيحة',
            'notes.max' => 'يجب ألا تتجاوز الملاحظات 1000 حرف',
            'payment_proof.image' => 'يجب أن تكون صورة إثبات الدفع من نوع صورة صحيح',
            'payment_proof.max' => 'يجب ألا يتجاوز حجم الصورة 5 ميجابايت',
            'payment_proof.mimes' => 'يجب أن تكون صورة إثبات الدفع من نوع: jpeg, jpg, png, gif, webp',
            'requested_start_date.required' => 'يجب تحديد تاريخ بداية الاشتراك',
            'requested_start_date.date' => 'تاريخ بداية الاشتراك غير صحيح',
            'requested_start_date.after_or_equal' => 'يجب أن يكون تاريخ بداية الاشتراك اليوم أو في المستقبل',
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
            'pricing_plan_id' => 'خطة الاشتراك',
            'notes' => 'الملاحظات',
            'payment_proof' => 'صورة إثبات الدفع',
            'requested_start_date' => 'تاريخ بداية الاشتراك',
        ];
    }
}
