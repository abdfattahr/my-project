<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AdvertisementResource;
use App\Filament\Resources\DeliveryWorkerResource as ResourcesDeliveryWorkerResource;
use App\Filament\Resources\MainCategorieResource as ResourcesMainCategorieResource;
use App\Filament\Resources\OrderResource as ResourcesOrderResource;
use App\Filament\Resources\ProductResource as ResourcesProductResource;
use App\Filament\Resources\SubcategorieResource as ResourcesSubcategorieResource;
use App\Filament\Resources\TradeMarkResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Vendor\Pages\Dashboard;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->login()
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Vendor/Resources'), for: 'App\\Filament\\Vendor\\Resources')
            ->discoverPages(in: app_path('Filament/Vendor/Pages'), for: 'App\\Filament\\Vendor\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->resources([
                ResourcesProductResource::class,
                ResourcesMainCategorieResource::class,
                ResourcesSubcategorieResource::class,
                TradeMarkResource::class,
                AdvertisementResource::class,
                ResourcesDeliveryWorkerResource::class,
                ResourcesOrderResource::class,
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
            ])
            ->authGuard('vendor')
            ->navigationGroups([
                'إدارة المنتجات',
                'الإعلانات',
                'إدارة التوصيل',
                'الطلبات',
            ]);
    }
}
