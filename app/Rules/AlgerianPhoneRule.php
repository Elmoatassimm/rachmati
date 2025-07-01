<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

class AlgerianPhoneRule implements ValidationRule
{
    private $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if phone is just "0" or "+213"
        if ($value === '0' || $value === '+213') {
            $fail('رقم الهاتف غير صالح.');
            return;
        }

        // Normalize the phone number for validation
        $normalizedPhone = $this->normalizePhoneNumber($value);
        
        // Validate Algerian phone number format
        if (!preg_match('/^\+213[567]\d{8}$/', $normalizedPhone)) {
            $fail('تنسيق رقم الهاتف غير صالح. يجب أن يبدأ بـ 05، 06، أو 07.');
            return;
        }

        // Check for uniqueness (considering different formats)
        $existingUser = User::where(function ($query) use ($value, $normalizedPhone) {
            $query->where('phone', $value)
                  ->orWhere('phone', $normalizedPhone)
                  ->orWhere('phone', $this->toLocalFormat($normalizedPhone));
        });

        // Exclude current user if updating
        if ($this->userId) {
            $existingUser->where('id', '!=', $this->userId);
        }

        if ($existingUser->exists()) {
            $fail('رقم الهاتف مُستخدم من قبل.');
        }
    }

    /**
     * Normalize phone number to +213 format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Convert to +213 format
        if (substr($phone, 0, 3) === '213') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '+213' . substr($phone, 1);
        } else {
            return '+213' . $phone;
        }
    }

    /**
     * Convert to local format (0XXXXXXXX)
     */
    private function toLocalFormat(string $normalizedPhone): string
    {
        if (substr($normalizedPhone, 0, 4) === '+213') {
            return '0' . substr($normalizedPhone, 4);
        }
        return $normalizedPhone;
    }
}
