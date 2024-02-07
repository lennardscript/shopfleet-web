<?php

namespace App\Notifications\UserNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $token;
    private $email;
    private $username;

    public function __construct($token, $email, $username)
    {
        $this->token = $token;
        $this->email = $email;
        $this->username = $username;
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
                    ->subject('Restablecer contraseña de cuenta de ShopFleet')
                    ->greeting("¡Hola {$this->username} 👋!")
                    ->line('Está recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña de su cuenta de ShopFleet. Si hiciste la solicitud, puedes restablecer tu contraseña haciendo clic en el botón de abajo. Este enlace expirará en 60 minutos y solo se puede utilizar una vez.')
                    ->action('Restablezca su contraseña aquí', url('/reset-password/' . $this->token . '?email=' . $this->email))
                    ->line('Si no solicitó un restablecimiento de contraseña, no es necesario realizar ninguna otra acción. Este correo electrónico es simplemente una medida de seguridad para tu cuenta. Gracias por usar ShopFleet. ¡Hasta pronto ❤️!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
