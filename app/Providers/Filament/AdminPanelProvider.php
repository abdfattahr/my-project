<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->profile()
            // ->userMenuItems([
            // MenuItem::make()
            //     ->label('setting')

            // ])
            // ->brandLogo('storage\IMG_20250702_172443_401.png')
            ->favicon('storage\IMG_20250702_172443_401.png')
            ->brandName('  Opty Market ')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->sidebarCollapsibleOnDesktop() //! جعل اللوحة الجانبية قابلة للطي
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string =>
                '<h2 class="text-gray text-2xl font-bold text-center mb-6">تسجيل الدخول إلى متجرك</h2>'
            )
                        ->colors([

                            'primary' => Color::Purple,
                        ])


            ->font('tajawl')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

    ->widgets([
    \App\Filament\Widgets\OrdersCustomersChart::class,
    \App\Filament\Widgets\OrdersCustomersStats::class,
    Widgets\AccountWidget::class,
    \App\Filament\Widgets\StatsOverview::class,
    \App\Filament\Widgets\LatestSupermarkets::class,
    \App\Filament\Widgets\LatestProducts::class,
    \App\Filament\Widgets\LatestAdvertisements::class,
    \App\Filament\Widgets\RecentOrders::class,
    \App\Filament\Widgets\OrderStats::class,
])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,

            ]);


    }
}
