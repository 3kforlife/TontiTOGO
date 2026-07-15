<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // N'enregistrer le transport Brevo que si c'est le mailer actif
        // Évite l'erreur AbstractHttpTransport quand MAIL_MAILER != brevo
        if (config('mail.default') !== 'brevo') {
            return;
        }

        Mail::extend('brevo', function () {
            return (new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory)->create(
                new \Symfony\Component\Mailer\Transport\Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key'),
                )
            );
        });
    }
}
