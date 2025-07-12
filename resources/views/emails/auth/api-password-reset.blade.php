<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز إعادة تعيين كلمة المرور - رشماتي</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }
        .logo img {
            max-width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 20px;
        }
        .verification-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .code-label {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #9ca3af;
            font-size: 14px;
            margin: 5px 0;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
            font-size: 14px;
        }
        .security-note {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #1e40af;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logo.png') }}" alt="رشماتي">
            </div>
            <h1>رشماتي</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                مرحباً {{ $user->name }}،
            </div>
            
            <div class="message">
                تلقيت هذا البريد الإلكتروني لأنه تم طلب إعادة تعيين كلمة المرور لحسابك في تطبيق رشماتي.
            </div>
            
            <div class="code-label">
                رمز التحقق الخاص بك:
            </div>
            
            <div class="verification-code">
                {{ $code }}
            </div>
            
            <div class="message">
                استخدم هذا الرمز في التطبيق لإعادة تعيين كلمة المرور الخاصة بك.
            </div>
            
            <div class="warning">
                <strong>تنبيه:</strong> رمز التحقق صالح لمدة 15 دقيقة فقط من وقت الإرسال.
            </div>
            
            <div class="security-note">
                <strong>أمان حسابك مهم:</strong>
                <ul style="margin: 10px 0; padding-right: 20px;">
                    <li>لا تشارك هذا الرمز مع أي شخص آخر</li>
                    <li>إذا لم تطلب إعادة تعيين كلمة المرور، تجاهل هذا البريد</li>
                    <li>تأكد من استخدام كلمة مرور قوية وفريدة</li>
                </ul>
            </div>
            
            <div class="message">
                إذا لم تطلب إعادة تعيين كلمة المرور، فلا حاجة لاتخاذ أي إجراء. حسابك آمن ولن يتم تغيير أي شيء.
            </div>
        </div>
        
        <div class="footer">
            <p><strong>منصة رشماتي</strong></p>
            <p>منصة الرشمات الرقمية الأولى في الجزائر</p>
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً، يرجى عدم الرد عليه.</p>
        </div>
    </div>
</body>
</html>
