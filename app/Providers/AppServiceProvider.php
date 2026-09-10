<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        \App\Models\BonusLog::created(function (\App\Models\BonusLog $log) {
            if ($log->category !== 'incentive' && $log->category !== 'penarikan') {
                if ($log->user) {
                    \App\Services\IncentiveService::check($log->user);
                }
            }
        });
    }
}
