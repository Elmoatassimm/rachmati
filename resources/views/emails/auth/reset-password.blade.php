<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور - رشماتي</title>
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
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background-color: #8b5cf6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #374151;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #6b7280;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
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
                مرحباً،
            </div>
            
            <div class="message">
                تلقيت هذا البريد الإلكتروني لأنه تم طلب إعادة تعيين كلمة المرور لحسابك في منصة رشماتي.
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">
                    إعادة تعيين كلمة المرور
                </a>
            </div>
            
            <div class="warning">
                <strong>تنبيه:</strong> رابط إعادة تعيين كلمة المرور صالح لمدة {{ config('auth.passwords.users.expire') }} دقيقة فقط.
            </div>
            
            <div class="message">
                إذا لم تطلب إعادة تعيين كلمة المرور، فلا حاجة لاتخاذ أي إجراء. حسابك آمن.
            </div>
            
            <div class="message">
                إذا كنت تواجه مشكلة في النقر على زر "إعادة تعيين كلمة المرور"، انسخ والصق الرابط أدناه في متصفح الويب الخاص بك:
            </div>
            
            <div style="word-break: break-all; color: #6b7280; font-size: 14px; margin: 15px 0;">
                {{ $url }}
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
