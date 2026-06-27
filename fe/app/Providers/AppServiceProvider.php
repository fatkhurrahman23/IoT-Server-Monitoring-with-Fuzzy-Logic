<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentIcon;

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
        FilamentIcon::register([
            'panels::sidebar.collapse-button' => 'heroicon-o-bars-3',
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
        ]);
    }
}
