<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseReset;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseReset
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('Restablecer contraseña de :app', ['app' => config('app.name')]))
            ->greeting(__('¡Hola!'))
            ->line(__('Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en :app.', [
                'app' => config('app.name'),
            ]))
            ->action(__('Recuperar contraseña'), $url)
            ->line(__('Este enlace expirará en :count minutos.', [
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]))
            ->line(__('Si no solicitaste este cambio, puedes ignorar este correo.'))
            // ← Aquí sobreescribimos la salutation por defecto
            ->salutation('Saludos, Tickets Pro');
    }
}
