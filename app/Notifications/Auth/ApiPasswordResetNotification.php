<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiPasswordResetNotification extends Notification
{
    use Queueable;

    /**
     * The password reset verification code.
     */
    public $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('رمز إعادة تعيين كلمة المرور - رشماتي')
            ->view('emails.auth.api-password-reset', [
                'code' => $this->code,
                'user' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
        ];
    }
}
