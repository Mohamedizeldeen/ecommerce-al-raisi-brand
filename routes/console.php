<?php

use App\Services\CartService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:expire-stale')->hourly();
Schedule::call(fn () => app(CartService::class)->pruneExpired())->daily();
