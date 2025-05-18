<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApiResetPasswordNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail']; 
    }

    public function toMail($notifiable)
    {
        $resetUrl = 'http://127.0.0.1:5501/frontend/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

         return (new MailMessage)
            ->subject('🔐 Reset Your Password - Assignment Portal')
            ->greeting('👋 Hello, ' . $notifiable->email)
            ->line('We received a request to reset your password. Click the button below to proceed.')
            ->action('Reset Password Now', $resetUrl)
            ->line('⚠️ If you did not request this, please ignore this email.')
            ->salutation('Thanks, Task Submission System Team');
    }
}
