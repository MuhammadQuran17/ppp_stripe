<?php

namespace MuhammadUmar\PPPStripe;

use Illuminate\Support\ServiceProvider;
use MuhammadUmar\PPPStripe\Commands\ImportPPPData;
use MuhammadUmar\PPPStripe\Services\Pricing\PPPService;
use MuhammadUmar\PPPStripe\Services\Security\ProxyIpDetectionService;

class PPPStripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services
        $this->app->singleton(ProxyIpDetectionService::class, function ($app) {
            return new ProxyIpDetectionService();
        });

        $this->app->singleton(PPPService::class, function ($app) {
            return new PPPService(
                $app->make(ProxyIpDetectionService::class),
                $app->make('request')
            );
        });
    }

    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'ppp-stripe-migrations');

        // Publish config
        $this->publishes([
            __DIR__ . '/Config/subscription-plans.php' => config_path('subscription-plans.php'),
        ], 'ppp-stripe-config');

        // Publish CSV data
        $this->publishes([
            __DIR__ . '/Resources/ppp_world.csv' => storage_path('app/private/ppp_world.csv'),
        ], 'ppp-stripe-data');

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportPPPData::class,
            ]);
        }

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/Config/subscription-plans.php',
            'subscription-plans'
        );
    }
}
