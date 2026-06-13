<?php

namespace App\Providers;

use App\Auth\DatabaseTokenRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $this->app->bind('auth.password.tokens', function ($app) {
            $hash = $app['hash'];
            $table = $app['config']['auth.passwords.users.table'];
            $expire = $app['config']['auth.passwords.users.expire'];
            $connection = $app['db']->connection();

            return new DatabaseTokenRepository($connection, $hash, $table, $expire);
        });
    }
}
