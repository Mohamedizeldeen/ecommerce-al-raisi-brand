<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Listeners\SendOrderConfirmation;
use App\Services\Payments\ThawaniService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ThawaniService::class,
            fn () => ThawaniService::fromConfig(),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            OrderPaid::class,
            SendOrderConfirmation::class,
        );
    }
}
