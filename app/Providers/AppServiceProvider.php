<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Filament\Resources\UserResource;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */

    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Filament::registerResources([
            UserResource::class,

        ]);

        Filament::registerRenderHook(
            'panels::auth.login.form.after',
            fn (): View => view('filament.login_extra')
        );

    }
}
