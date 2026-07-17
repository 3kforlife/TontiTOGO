<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderSmsJob;
use App\Models\Organization;
use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Commande : envoie les SMS de rappel pour chaque organisation
 * dont l'heure configurée correspond à l'heure courante.
 *
 * Doit être lancée chaque minute par le Scheduler Laravel.
 * Le Scheduler doit lui-même être déclenché par un cron externe :
 *   * * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1
 */
class SendScheduledReminders extends Command
{
    protected $signature   = 'reminders:send';
    protected $description = 'Envoie les SMS de rappel aux membres en retard selon l\'heure configurée';

    public function handle(): int
    {
        $currentTime = now()->format('H:i');

        // Récupérer toutes les organisations qui ont configuré cette heure
        $settings = Setting::where('key', 'sms_reminder_time')
            ->where('value', $currentTime)
            ->get();

        if ($settings->isEmpty()) {
            $this->line("Aucune organisation configurée pour {$currentTime}.");
            return self::SUCCESS;
        }

        foreach ($settings as $setting) {
            SendReminderSmsJob::dispatch($setting->organization_id);
            $this->info("Rappels SMS dispatchés pour l'organisation #{$setting->organization_id}");
        }

        return self::SUCCESS;
    }
}
