<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->buildFrontendVerificationUrl($notifiable);

        return (new MailMessage())
            ->subject('TontiTOGO — Vérifiez votre adresse e-mail')
            ->greeting("Bonjour {$notifiable->full_name},")
            ->line('Merci de vous être inscrit sur **TontiTOGO**.')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour confirmer votre adresse e-mail et activer votre compte.')
            ->action('Vérifier mon adresse e-mail', $verificationUrl)
            ->line('Ce lien de vérification expirera dans **' . (Config::get('auth.verification.expire', 60)) . ' minutes**.')
            ->line('Si vous n\'avez pas créé de compte sur TontiTOGO, aucune action n\'est requise de votre part.')
            ->salutation('Cordialement, L\'équipe TontiTOGO');
    }

    private function buildFrontendVerificationUrl($notifiable): string
    {
        $backendUrl = URL::temporarySignedRoute(
            'responsible.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $frontendBase = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');

        return $frontendBase . '/email/verify?url=' . urlencode($backendUrl);
    }
}
