<?php

namespace MuhammadUmar\PPPGateway;

use Illuminate\Support\ServiceProvider;
use MuhammadUmar\PPPGateway\Commands\ImportPPPData;
use MuhammadUmar\PPPGateway\Services\Pricing\PPPService;
use MuhammadUmar\PPPGateway\Services\Security\ProxyIpDetectionService;
use Illuminate\Support\Facades\Route;
use MuhammadUmar\PPPGateway\Http\Controllers\PurchaseController;

class PPPGatewayServiceProvider extends ServiceProvider
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
        $this->publishes([
            __DIR__ . '/Database/Migrations' => database_path('migrations'),
        ], 'ppp-gateway-migrations');

        $this->publishes([
            __DIR__ . '/Config/subscription-plans.php' => config_path('subscription-plans.php'),
        ], 'ppp-gateway-config');

        // Publish CSV data
        $this->publishes([
            __DIR__ . '/Resources/ppp_world.csv' => storage_path('app/private/ppp_world.csv'),
        ], 'ppp-gateway-data');

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        Route::middleware('auth')->group(function () {
            Route::post('purchase', PurchaseController::class)->name('ppp-gateway.purchase');
        });

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportPPPData::class,
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__ . '/Config/subscription-plans.php',
            'subscription-plans'
        );
    }
}
