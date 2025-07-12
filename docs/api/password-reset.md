# Password Reset API Documentation

## Overview

The Password Reset API provides a secure way for client users to reset their passwords using a 6-digit verification code sent via email. This system is designed specifically for mobile app clients and includes proper security measures, rate limiting, and Arabic localization.

## Security Features

- **6-digit verification codes** with 15-minute expiration
- **Rate limiting**: 5 requests per minute per IP
- **Client-only access**: Only users with `user_type = 'client'` can use these endpoints
- **Single-use codes**: Codes are marked as used after successful password reset
- **Automatic cleanup**: Expired codes are automatically removed
- **Code replacement**: New requests replace existing unused codes for the same email

## Endpoints

### 1. Request Password Reset Code

**POST** `/api/forgot-password`

Sends a 6-digit verification code to the user's email address.

#### Request Body

```json
{
    "email": "user@example.com"
}
```

#### Validation Rules

- `email`: required, valid email format, must exist in users table

#### Success Response (200)

```json
{
    "success": true,
    "message": "تم إرسال رمز التحقق إلى بريدك الإلكتروني"
}
```

#### Error Responses

**422 - Validation Error**
```json
{
    "success": false,
    "message": "أخطاء في التحقق من البيانات",
    "errors": {
        "email": ["البريد الإلكتروني غير مسجل في النظام."]
    }
}
```

**403 - Forbidden (Non-client user)**
```json
{
    "success": false,
    "message": "غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان"
}
```

**429 - Rate Limited**
```json
{
    "message": "Too Many Attempts.",
    "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException"
}
```

### 2. Reset Password with Verification Code

**POST** `/api/reset-password`

Resets the user's password using the verification code received via email.

#### Request Body

```json
{
    "email": "user@example.com",
    "code": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Validation Rules

- `email`: required, valid email format, must exist in users table
- `code`: required, exactly 6 characters
- `password`: required, minimum 8 characters, must be confirmed
- `password_confirmation`: required, must match password

#### Success Response (200)

```json
{
    "success": true,
    "message": "تم تغيير كلمة المرور بنجاح"
}
```

#### Error Responses

**422 - Validation Error**
```json
{
    "success": false,
    "message": "أخطاء في التحقق من البيانات",
    "errors": {
        "password": ["كلمة المرور يجب أن تكون 8 أحرف على الأقل."]
    }
}
```

**400 - Invalid/Expired Code**
```json
{
    "success": false,
    "message": "رمز التحقق غير صحيح أو منتهي الصلاحية"
}
```

**403 - Forbidden (Non-client user)**
```json
{
    "success": false,
    "message": "غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان"
}
```

## Email Template

The verification code is sent using a branded Arabic email template that includes:

- **Rashmaati branding** with logo
- **RTL layout** for proper Arabic display
- **Security warnings** and best practices
- **15-minute expiration notice**
- **Professional styling** matching existing email templates

## Database Schema

### password_reset_codes Table

```sql
CREATE TABLE password_reset_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    used_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_email_code (email, code),
    INDEX idx_email_expires (email, expires_at)
);
```

## Usage Examples

### Mobile App Integration

```javascript
// Request password reset
const forgotPassword = async (email) => {
    try {
        const response = await fetch('/api/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message and navigate to code input screen
            showMessage(data.message);
            navigateToCodeInput();
        } else {
            // Show error message
            showError(data.message);
        }
    } catch (error) {
        showError('حدث خطأ في الشبكة');
    }
};

// Reset password with code
const resetPassword = async (email, code, password, passwordConfirmation) => {
    try {
        const response = await fetch('/api/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email,
                code,
                password,
                password_confirmation: passwordConfirmation,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message and navigate to login
            showMessage(data.message);
            navigateToLogin();
        } else {
            // Show error message
            showError(data.message);
        }
    } catch (error) {
        showError('حدث خطأ في الشبكة');
    }
};
```

## Testing

Comprehensive test suite covers:

- ✅ Valid password reset flow
- ✅ Invalid email validation
- ✅ Non-client user restrictions
- ✅ Code expiration handling
- ✅ Code reuse prevention
- ✅ Rate limiting
- ✅ Password validation
- ✅ Arabic error messages

Run tests with:
```bash
php artisan test tests/Feature/Api/PasswordResetApiTest.php
```

## Security Considerations

1. **Rate Limiting**: Prevents brute force attacks
2. **Client-Only Access**: Restricts access to mobile app users only
3. **Short Expiration**: 15-minute code validity reduces attack window
4. **Single Use**: Codes cannot be reused
5. **Secure Generation**: Cryptographically secure random code generation
6. **Email Validation**: Ensures codes are only sent to valid, registered emails
7. **Automatic Cleanup**: Expired codes are automatically removed from database

## Maintenance

### Cleanup Expired Codes

The system automatically cleans up expired codes during password reset operations. For additional cleanup, you can run:

```php
// In a scheduled job or command
PasswordResetCode::cleanupExpired();
```

### Monitoring

Monitor the following metrics:
- Password reset request frequency
- Success/failure rates
- Code expiration rates
- Rate limiting triggers
