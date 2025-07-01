<?php

namespace App\Filament\Widgets;

use App\Models\Advertisement;
use App\Models\Customer;
use App\Models\DeliveryWorker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Supermarket;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\TradeMark;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    protected static ?string $title = 'إحصائيات عامة';

    protected function getStats(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $supermarketId = $user && $user->hasRole('vendor') ? optional($user->supermarket)->id : null;

        $invoiceQuery = Invoice::query();
        $productQuery = Product::query();

        if ($user && $user->hasRole('vendor') && $supermarketId) {
            $invoiceQuery->whereHas('orders', function (Builder $query) use ($supermarketId) {
                $query->whereHas('product', function (Builder $subQuery) use ($supermarketId) {
                    $subQuery->whereHas('supermarkets', function (Builder $subSubQuery) use ($supermarketId) {
                        $subSubQuery->where('supermarkets.id', $supermarketId);
                    });
                });
            });
            $productQuery->whereHas('supermarkets', function (Builder $query) use ($supermarketId) {
                $query->where('supermarkets.id', $supermarketId);
            });
        }

        $acceptedCount = $invoiceQuery->clone()->where('status', 'accepted')->count() >0 ;
        $pendingCount = $invoiceQuery->clone()->where('status', 'pending')->count()>0;
        $cancelledCount = $invoiceQuery->clone()->where('status', 'cancelled')->count()>0;
        $productCount = $productQuery->count();

        $stats = [
            Stat::make('الطلبات المقبولة', $acceptedCount)
                ->description($acceptedCount > 0 ? 'عدد الطلبات المقبولة' : 'لا توجد طلبات مقبولة حاليًا')
                ->color('success')
                ->descriptionIcon('heroicon-m-check-badge'),
            Stat::make('الطلبات المعلقة', $pendingCount)
                ->description($pendingCount > 0 ? 'عدد الطلبات المعلقة' : 'لا توجد طلبات معلقة حاليًا')
                ->descriptionIcon('heroicon-c-exclamation-circle')
                ->color('warning'),
            Stat::make('الطلبات الملغاة', $cancelledCount)
                ->description($cancelledCount > 0 ? 'عدد الطلبات المرفوضة' : 'لا توجد طلبات ملغاة حاليًا')
                ->color('danger')
                ->descriptionIcon('heroicon-c-no-symbol'),
            Stat::make('عدد المنتجات', $productCount)
                ->description($productCount > 0 ? 'إجمالي المنتجات المتوفرة' : 'لا توجد منتجات متاحة حاليًا')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->color('primary')
                ->extraAttributes(['class' => 'stat-card-primary']),
        ];

        if ($user && $user->hasRole('admin')) {
            $stats = array_merge($stats, [
                Stat::make('إجمالي الطلبات', Invoice::query()->count())
                    ->description(Invoice::query()->count() > 0 ? 'عدد الطلبات الكلي' : 'لا توجد طلبات حاليًا')
                    ->color('primary'),
                Stat::make('عدد المتاجر', Supermarket::count())
                    ->description(Supermarket::count() > 0 ? 'إجمالي المتاجر المسجلة' : 'لا توجد متاجر مسجلة حاليًا')
                    ->descriptionIcon('heroicon-o-building-storefront')
                    ->color('success')
                    ->extraAttributes(['class' => 'stat-card-success']),
                Stat::make('عدد الزبائن', Customer::count())
                    ->description(Customer::count() > 0 ? 'إجمالي الزبائن المسجلين' : 'لا يوجد زبائن مسجلين حاليًا')
                    ->descriptionIcon('heroicon-o-users')
                    ->color('warning')
                    ->extraAttributes(['class' => 'stat-card-warning']),
                Stat::make('عدد العلامات التجارية', TradeMark::count())
                    ->description(TradeMark::count() > 0 ? 'إجمالي العلامات التجارية' : 'لا توجد علامات تجارية حاليًا')
                    ->descriptionIcon('heroicon-o-tag')
                    ->color('danger')
                    ->extraAttributes(['class' => 'stat-card-danger']),
                Stat::make('عدد الإعلانات', Advertisement::count())
                    ->description(Advertisement::count() > 0 ? 'إجمالي الإعلانات' : 'لا توجد إعلانات حاليًا')
                    ->descriptionIcon('heroicon-o-megaphone')
                    ->color('warning')
                    ->extraAttributes(['class' => 'stat-card-warning']),
                Stat::make('عدد عمال التوصيل', DeliveryWorker::count())
                    ->description(DeliveryWorker::count() > 0 ? 'إجمالي عمال التوصيل' : 'لا يوجد عمال توصيل حاليًا')
                    ->descriptionIcon('heroicon-o-truck')
                    ->color('primary')
                    ->extraAttributes(['class' => 'stat-card-primary']),
            ]);
        }

        return $stats;
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }
}
