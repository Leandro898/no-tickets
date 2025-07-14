<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class MagicLinkLogin extends Notification
{
    use Queueable;

    /**
     * The magic link token.
     *
     * @var string
     */
    protected string $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'magic.login',
            Carbon::now()->addMinutes(60),
            ['token' => $this->token]
        );

        return (new MailMessage)
            ->subject('Tu enlace de acceso a Tickets Pro')
            ->line('Haz clic en el siguiente botón para entrar directamente:')
            ->action('Entrar a Tickets Pro', $url)
            ->line('Este enlace expirará en 60 minutos.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
