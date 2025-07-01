<?php

namespace App\Http\Requests\Designer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRachmaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated and has an active designer profile
        if (!Auth::check() || !Auth::user()->designer || !Auth::user()->designer->isActive()) {
            return false;
        }
        
        // Get rachma from route using model binding - Laravel resource routes use the model name
        $rachma = $this->route('rachmat');
        
        if (!$rachma) {
            return false;
        }
        
        // Find the rachma and check if it belongs to the authenticated designer
        $rachma = \App\Models\Rachma::find($rachma);
        
        if (!$rachma || $rachma->designer_id !== Auth::user()->designer->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Multilingual title support - Arabic is required, French is optional
            'title_ar' => 'nullable|string|max:255',
            'title_fr' => 'nullable|string|max:255',
            
            // Multilingual description support - both optional
            'description_ar' => 'nullable|string|max:2000',
            'description_fr' => 'nullable|string|max:2000',
            
            'categories' => 'nullable|array|min:1',
            'categories.*' => 'exists:categories,id',
            
            // Only width and height, no size field
            'width' => 'nullable|numeric|min:0|max:9999.99',
            'height' => 'nullable|numeric|min:0|max:9999.99',
            
            'gharazat' => 'nullable|integer|min:1|max:1000000',
            'color_numbers' => 'nullable|integer|min:1|max:100',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            
            // File validation (optional for updates) - using new files structure
            'files' => 'nullable|array|max:20',
            'files.*' => [
                'file',
                'max:10240', // 10MB max
                'mimes:zip,rar,dst,exp,jef,pes,vp3,xxx,hus,vip,sew,csd,pdf'
            ],
            
            // Preview images validation (optional for updates)
            'preview_images' => 'nullable|array|max:10',
            'preview_images.*' => [
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048', // 2MB max
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
            ],
            
            // Files and images to be removed
            'remove_preview_images' => 'nullable|array',
            'remove_preview_images.*' => 'string',
            'remove_files' => 'nullable|array',
            'remove_files.*' => 'integer',
            
            // Parts validation - only multilingual names
            'parts' => 'nullable|array|max:50',
            'parts.*.name_ar' => 'nullable|string|max:255',
            'parts.*.name_fr' => 'nullable|string|max:255',
            'parts.*.length' => 'nullable|numeric|min:0|max:9999.99',
            'parts.*.height' => 'nullable|numeric|min:0|max:9999.99',
            'parts.*.stitches' => 'nullable|integer|min:1|max:1000000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            // Title validation
            'title_ar.required' => 'عنوان الرشمة مطلوب',
            'title_ar.string' => 'عنوان الرشمة يجب أن يكون نص',
            'title_ar.max' => 'عنوان الرشمة يجب ألا يتجاوز 255 حرف',
            
            // Description validation
            'description_ar.string' => 'وصف الرشمة يجب أن يكون نص',
            'description_ar.max' => 'وصف الرشمة يجب ألا يتجاوز 2000 حرف',
            
            // Categories validation
            'categories.required' => 'يجب اختيار تصنيف واحد على الأقل',
            'categories.array' => 'التصنيفات يجب أن تكون قائمة',
            'categories.min' => 'يجب اختيار تصنيف واحد على الأقل',
            'categories.*.exists' => 'التصنيف المحدد غير موجود',
            
            // Gharazat validation
            'gharazat.required' => 'عدد الغرز مطلوب',
            'gharazat.integer' => 'عدد الغرز يجب أن يكون رقم صحيح',
            'gharazat.min' => 'عدد الغرز يجب أن يكون أكبر من صفر',
            'gharazat.max' => 'عدد الغرز يجب ألا يتجاوز مليون غرزة',
            
            // Color numbers validation
            'color_numbers.required' => 'عدد الألوان مطلوب',
            'color_numbers.integer' => 'عدد الألوان يجب أن يكون رقم صحيح',
            'color_numbers.min' => 'عدد الألوان يجب أن يكون أكبر من صفر',
            'color_numbers.max' => 'عدد الألوان يجب ألا يتجاوز 100 لون',
            
            // Price validation
            'price.required' => 'سعر الرشمة مطلوب',
            'price.numeric' => 'سعر الرشمة يجب أن يكون رقم',
            'price.min' => 'سعر الرشمة يجب أن يكون أكبر من أو يساوي صفر',
            'price.max' => 'سعر الرشمة يجب ألا يتجاوز 999,999.99',
            
            // Files validation
            'files.array' => 'ملفات الرشمة يجب أن تكون قائمة',
            'files.max' => 'يمكن رفع 20 ملف كحد أقصى',
            'files.*.file' => 'يجب أن يكون ملف صالح',
            'files.*.max' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
            'files.*.mimes' => 'نوع الملف غير مدعوم. الأنواع المدعومة: ZIP, RAR, DST, EXP, JEF, PES, VP3, XXX, HUS, VIP, SEW, CSD, PDF',
            
            // Preview images validation
            'preview_images.array' => 'صور المعاينة يجب أن تكون قائمة',
            'preview_images.max' => 'يمكن رفع 10 صور معاينة كحد أقصى',
            'preview_images.*.image' => 'يجب أن يكون ملف صورة صالح',
            'preview_images.*.mimes' => 'نوع الصورة غير مدعوم. الأنواع المدعومة: JPEG, PNG, JPG, WEBP',
            'preview_images.*.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'preview_images.*.dimensions' => 'أبعاد الصورة يجب أن تكون بين 100x100 و 4000x4000 بكسل',
            
            // Parts validation
            'parts.array' => 'أجزاء الرشمة يجب أن تكون قائمة',
            'parts.max' => 'يمكن إضافة 50 جزء كحد أقصى',
            'parts.*.name_ar.required' => 'اسم الجزء مطلوب',
            'parts.*.name_ar.string' => 'اسم الجزء يجب أن يكون نص',
            'parts.*.name_ar.max' => 'اسم الجزء يجب ألا يتجاوز 255 حرف',
            'parts.*.length.numeric' => 'طول الجزء يجب أن يكون رقم',
            'parts.*.length.min' => 'طول الجزء يجب أن يكون أكبر من أو يساوي صفر',
            'parts.*.length.max' => 'طول الجزء يجب ألا يتجاوز 9999.99',
            'parts.*.height.numeric' => 'ارتفاع الجزء يجب أن يكون رقم',
            'parts.*.height.min' => 'ارتفاع الجزء يجب أن يكون أكبر من أو يساوي صفر',
            'parts.*.height.max' => 'ارتفاع الجزء يجب ألا يتجاوز 9999.99',
            'parts.*.stitches.required' => 'عدد الغرز مطلوب لكل جزء',
            'parts.*.stitches.integer' => 'عدد الغرز يجب أن يكون رقم صحيح',
            'parts.*.stitches.min' => 'عدد الغرز يجب أن يكون أكبر من صفر',
            'parts.*.stitches.max' => 'عدد الغرز يجب ألا يتجاوز مليون غرزة',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title_ar' => 'عنوان الرشمة',
            'description_ar' => 'وصف الرشمة',
            'categories' => 'التصنيفات',
            'width' => 'العرض',
            'height' => 'الطول',
            'gharazat' => 'عدد الغرز',
            'color_numbers' => 'عدد الألوان',
            'price' => 'السعر',
            'files' => 'ملفات الرشمة',
            'preview_images' => 'صور المعاينة',
            'parts' => 'أجزاء الرشمة',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(403, 'غير مصرح لك بتعديل هذه الرشمة');
    }
} 