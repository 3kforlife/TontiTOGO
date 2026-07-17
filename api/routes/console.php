<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled Tasks — TontiTOGO
|--------------------------------------------------------------------------
|
| Pour que ces tâches s'exécutent, un cron doit appeler chaque minute :
|   * * * * * php /var/www/html/artisan schedule:run >> /dev/null 2>&1
|
| Sur Render : configurer un Cron Job dans le dashboard
|   → Command : php /var/www/html/artisan schedule:run
|   → Schedule : Every minute (*/1 * * * *)
|
*/

// Vérifier chaque minute si des organisations ont configuré cette heure
// pour l'envoi des rappels SMS
Schedule::command('reminders:send')->everyMinute();
