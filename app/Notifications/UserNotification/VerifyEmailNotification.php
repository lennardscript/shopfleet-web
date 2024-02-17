<?php

namespace App\Notifications\userNotification;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    use Queueable;

    private $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    protected function verificationUrl($notifiable)
    {
        $url = url("/verify-email/{$notifiable->id_user}/{$notifiable->verification_token}");
        return $url;
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('¡Verifique su correo electrónico de ShopFleet 📧!')
            ->greeting("¡Bienvenido/a {$this->username} 👋!")
            ->line('Está recibiendo este correo electrónico porque recibimos una solicitud de verificación de su correo electrónico de ShopFleet porque se ha creado una cuenta en una de nuestras plataformas. Por favor, haga clic en el siguiente enlace para verificar su correo electrónico. Si no creó una cuenta, no es necesario realizar ninguna otra acción.')
            ->action('Verifique su correo electrónico 📧', $verificationUrl)
            ->line('Gracias por registrarse en ShopFleet. ¡Hasta pronto ❤️!')
            ->salutation('¡Saludos desde el equipo de ShopFleet 🚀!');
    }
}
