<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class IntouchResetPasswordNotification extends ResetPassword
{
    protected function resetUrl(mixed $notifiable): string
    {
        $baseUrl = config('app.intouch_password_reset_url')
            ?? (parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https') . '://' . config('app.intouch_domain');
        $baseUrl = rtrim($baseUrl, '/');

        return $baseUrl . '/wachtwoord-herstellen/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset());
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(Lang::get('Wachtwoord herstellen – Intouch Vierdaagse Kesteren'))
            ->line(Lang::get('Je ontvangt deze e-mail omdat we een wachtwoordherstelverzoek hebben ontvangen voor je account.'))
            ->action(Lang::get('Wachtwoord herstellen'), $url)
            ->line(Lang::get('Deze link verloopt over :count minuten.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
            ->line(Lang::get('Als je geen wachtwoordherstel hebt aangevraagd, kun je deze e-mail negeren.'));
    }
}
