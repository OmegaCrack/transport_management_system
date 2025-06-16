<?php

namespace App\Providers;

use AfricasTalking\SDK\AfricasTalking;
use App\Channels\AfricasTalkingSmsChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class AfricasTalkingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/africas_talking.php', 'africas_talking'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../../config/africas_talking.php' => config_path('africas_talking.php'),
        ], 'config');

        // Defer channel registration until needed
        $this->app->resolving('notification.channel', function ($service, $app) {
            $service->extend('africas_talking', function ($app) {
                $africasTalking = new AfricasTalking(
                    config('africas_talking.username'),
                    config('africas_talking.api_key'),
                    config('africas_talking.environment', 'sandbox')
                );

                return new AfricasTalkingSmsChannel(
                    $africasTalking,
                    config('africas_talking.sender_id')
                );
            });
        });
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['notification.channel'];
    }
}
