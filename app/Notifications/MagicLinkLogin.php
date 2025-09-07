<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class MagicLinkLogin extends Notification
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'magic.login',
            now()->addMinutes(60),
            ['user' => $notifiable->getKey()]
        );

        return (new MailMessage)
            ->subject('Te damos la bienvenida a ' . config('app.name') . '!')
            ->greeting("¡Hola, {$notifiable->name}!")    // ← aquí va el nombre
            ->line('Pulsa el botón para confirmar tu cuenta:')
            ->action('Acceder a ' . config('app.name'), $url)
            ->line('Este enlace expira en 60 minutos.')
            ->salutation('Saludos cordiales, Equipo ' . config('app.name'));
    }
}
