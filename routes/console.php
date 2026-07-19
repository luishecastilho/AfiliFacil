<?php

use App\Services\SubscriptionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(SubscriptionService::class)->resetMonthlyUsage())
    ->monthlyOn(1, '00:00')
    ->description('Reset monthly NF usage');
