<?php

namespace App\Http\Requests\Designer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRachmaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->designer && Auth::user()->designer->isActive();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Multilingual title support - Arabic is required, French is optional
            'title_ar' => 'required|string|max:255',
            'title_fr' => 'required|string|max:255',
            
            // Multilingual description support - both optional
            'description_ar' => 'nullable|string',
            'description_fr' => 'nullable|string',
            
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            
            'color_numbers' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            
            // File validation - using new files structure with security restrictions
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|mimes:zip,rar,dst,exp,jef,pes,vp3,xxx,hus,vip,sew,csd,pdf|max:10240',
            
            // Preview images validation
            'preview_images' => 'nullable|array',
            'preview_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Parts validation - only multilingual names
            'parts' => 'nullable|array',
            'parts.*.name_ar' => 'required|string|max:255',
            'parts.*.name_fr' => 'nullable|string|max:255',
            'parts.*.length' => 'nullable|numeric|min:0|max:9999.99',
            'parts.*.height' => 'nullable|numeric|min:0|max:9999.99',
            'parts.*.stitches' => 'required|integer|min:1',
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
            'title_ar.max' => 'العنوان باللغة العربية يجب ألا يتجاوز 255 حرف',
            'title_fr.string' => 'العنوان باللغة الفرنسية يجب أن يكون نص',
            'title_fr.max' => 'العنوان باللغة الفرنسية يجب ألا يتجاوز 255 حرف',
            
            // Description validation
            'description_ar.string' => 'الوصف باللغة العربية يجب أن يكون نص',
            'description_ar.max' => 'الوصف باللغة العربية يجب ألا يتجاوز 2000 حرف',
            'description_fr.string' => 'الوصف باللغة الفرنسية يجب أن يكون نص',
            'description_fr.max' => 'الوصف باللغة الفرنسية يجب ألا يتجاوز 2000 حرف',
            
            // Category validation
            'categories.required' => 'تصنيفات الرشمة مطلوبة',
            'categories.array' => 'تصنيفات الرشمة يجب أن تكون قائمة',
            'categories.min' => 'يجب اختيار تصنيف واحد على الأقل',
            'categories.*.exists' => 'أحد التصنيفات المختارة غير صحيح',
            
            // Color numbers validation
            'color_numbers.required' => 'عدد الألوان مطلوب',
            'color_numbers.integer' => 'عدد الألوان يجب أن يكون رقم صحيح',
            'color_numbers.min' => 'عدد الألوان يجب أن يكون أكبر من صفر',
            
            // Price validation
            'price.required' => 'سعر الرشمة مطلوب',
            'price.numeric' => 'سعر الرشمة يجب أن يكون رقم',
            'price.min' => 'سعر الرشمة يجب أن يكون أكبر من أو يساوي صفر',
            
            // Files validation
            'files.required' => 'يجب رفع ملف واحد على الأقل للرشمة',
            'files.array' => 'ملفات الرشمة يجب أن تكون قائمة',
            'files.min' => 'يجب رفع ملف واحد على الأقل للرشمة',
            'files.*.required' => 'الملف مطلوب',
            'files.*.file' => 'يجب أن يكون ملف صالح',
            'files.*.mimes' => 'نوع الملف غير مدعوم. الأنواع المدعومة: ZIP, RAR, DST, EXP, JEF, PES, VP3, XXX, HUS, VIP, SEW, CSD, PDF',
            'files.*.not_in' => 'نوع الملف غير مسموح به لأسباب أمنية. يرجى رفع ملفات الرشمة المدعومة فقط',
            
            // Preview images validation
            'preview_images.array' => 'صور المعاينة يجب أن تكون قائمة',
            'preview_images.*.image' => 'يجب أن يكون ملف صورة صالح',
            'preview_images.*.mimes' => 'نوع الصورة غير مدعوم. الأنواع المدعومة: JPEG, PNG, JPG, WEBP',
            'preview_images.*.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'preview_images.*.dimensions' => 'أبعاد الصورة يجب أن تكون بين 100x100 و 4000x4000 بكسل',
            
            // Parts validation
            'parts.array' => 'أجزاء الرشمة يجب أن تكون قائمة',
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
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title_ar' => 'عنوان الرشمة',
            'title_fr' => 'عنوان الرشمة بالفرنسية',
            'description_ar' => 'وصف الرشمة',
            'description_fr' => 'وصف الرشمة بالفرنسية',
            'categories' => 'التصنيفات',
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
        abort(403, 'يجب أن يكون اشتراكك نشطاً لرفع الرشمات. يرجى تجديد اشتراكك أولاً');
    }
} 