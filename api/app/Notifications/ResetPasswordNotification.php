<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->buildFrontendResetUrl($notifiable);

        return (new MailMessage())
            ->subject('TontiTOGO — Réinitialisation de votre mot de passe')
            ->greeting("Bonjour {$notifiable->full_name},")
            ->line('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation du mot de passe de votre compte TontiTOGO.')
            ->action('Réinitialiser mon mot de passe', $resetUrl)
            ->line('Ce lien de réinitialisation expirera dans **' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) . ' minutes**.')
            ->line('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action n\'est requise. Votre mot de passe restera inchangé.')
            ->line('⚠️ Ne partagez jamais ce lien avec quelqu\'un d\'autre.')
            ->salutation('Cordialement, L\'équipe TontiTOGO');
    }

    
    private function buildFrontendResetUrl($notifiable): string
    {
        $frontendBase = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');

        return $frontendBase . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
