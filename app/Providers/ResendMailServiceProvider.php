<?php

namespace App\Providers;

use App\Mail\Transport\ResendTransport;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;

class ResendMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app['mail.manager']->extend('resend', function (array $config) {
            return new ResendTransport(
                config('mail.mailers.resend.secret') ?? env('RESEND_API_KEY')
            );
        });
    }
}
