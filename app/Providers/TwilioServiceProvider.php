<?php

namespace App\Providers;

use App\Channels\TwilioSmsChannel;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;

class TwilioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(
                config('services.twilio.sid'),
                config('services.twilio.auth_token')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->when(TwilioSmsChannel::class)
            ->needs('$from')
            ->giveConfig('services.twilio.from');

        // Register the Twilio SMS channel
        $this->app->instance(
            TwilioSmsChannel::class,
            new TwilioSmsChannel(
                app(Client::class),
                config('services.twilio.from')
            )
        );
    }
}
