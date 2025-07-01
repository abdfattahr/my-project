<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class OrdersCustomersStats extends BaseWidget
{
    protected ?string $heading = 'إحصائيات الطلبات والعملاء';

    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        $isAdmin = auth()->user()->hasRole('admin'); // تعديل حسب طريقة التحقق
        $supermarketId = $isAdmin ? null : auth()->user()->supermarket->id;

        // الطلبات اليومية
        $todayOrdersQuery = Order::whereDate('created_at', Carbon::today());
        if (!$isAdmin) {
            $todayOrdersQuery->whereHas('invoice', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $todayOrders = $todayOrdersQuery->count();

        $yesterdayOrdersQuery = Order::whereDate('created_at', Carbon::yesterday());
        if (!$isAdmin) {
            $yesterdayOrdersQuery->whereHas('invoice', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $yesterdayOrders = $yesterdayOrdersQuery->count();

        $ordersChangeDaily = $yesterdayOrders > 0
            ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100
            : ($todayOrders > 0 ? 100 : 0);

        // العملاء الجدد (الربط عبر الفواتير)
        $todayCustomersQuery = Customer::whereDate('created_at', Carbon::today());
        if (!$isAdmin) {
            $todayCustomersQuery->whereHas('invoices', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $todayCustomers = $todayCustomersQuery->count();

        $yesterdayCustomersQuery = Customer::whereDate('created_at', Carbon::yesterday());
        if (!$isAdmin) {
            $yesterdayCustomersQuery->whereHas('invoices', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $yesterdayCustomers = $yesterdayCustomersQuery->count();

        $customersChangeDaily = $yesterdayCustomers > 0
            ? (($todayCustomers - $yesterdayCustomers) / $yesterdayCustomers) * 100
            : ($todayCustomers > 0 ? 100 : 0);

        // الطلبات الأسبوعية
        $weekOrdersQuery = Order::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
        if (!$isAdmin) {
            $weekOrdersQuery->whereHas('invoice', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $weekOrders = $weekOrdersQuery->count();

        $lastWeekOrdersQuery = Order::whereBetween('created_at', [
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek()
        ]);
        if (!$isAdmin) {
            $lastWeekOrdersQuery->whereHas('invoice', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }
        $lastWeekOrders = $lastWeekOrdersQuery->count();

        $ordersChangeWeekly = $lastWeekOrders > 0
            ? (($weekOrders - $lastWeekOrders) / $lastWeekOrders) * 100
            : ($weekOrders > 0 ? 100 : 0);

        // الإيرادات
        $totalRevenueQuery = Invoice::whereDate('created_at', Carbon::today());
        if (!$isAdmin) {
            $totalRevenueQuery->where('supermarket_id', $supermarketId);
        }
        $totalRevenue = $totalRevenueQuery->sum('total_price');

        return [
            Stat::make('الطلبات اليومية', $todayOrders)
                ->description(abs(round($ordersChangeDaily, 1)) . '% ' .
                    ($ordersChangeDaily >= 0 ? 'زيادة' : 'نقصان') . ' عن أمس')
                ->descriptionIcon($ordersChangeDaily >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChangeDaily >= 0 ? 'success' : 'danger')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->chart($this->getDailyOrdersChart($isAdmin, $supermarketId)),

            Stat::make('الطلبات الأسبوعية', $weekOrders)
                ->description(abs(round($ordersChangeWeekly, 1)) . '% ' .
                    ($ordersChangeWeekly >= 0 ? 'زيادة' : 'نقصان') . ' عن الأسبوع الماضي')
                ->descriptionIcon($ordersChangeWeekly >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChangeWeekly >= 0 ? 'success' : 'warning')
                ->extraAttributes(['class' => 'cursor-pointer']),

            Stat::make('زبائن جدد اليوم', $todayCustomers)
                ->description(abs(round($customersChangeDaily, 1)) . '% ' .
                    ($customersChangeDaily >= 0 ? 'زيادة' : 'نقصان') . ' عن أمس')
                ->descriptionIcon($customersChangeDaily >= 0 ? 'heroicon-m-users' : 'heroicon-m-user-minus')
                ->color($customersChangeDaily >= 0 ? 'success' : 'danger')
                ->chart($this->getDailyCustomersChart($isAdmin, $supermarketId)),

            Stat::make('إجمالي الإيرادات اليوم', number_format($totalRevenue, 2) . ' ل.س')
            
                ->description('اليوم الحالي')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => '$emit("showRevenueDetails")'
                ]),
        ];
    }

    protected function getDailyOrdersChart(bool $isAdmin, ?int $supermarketId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $query = Order::whereDate('created_at', Carbon::today()->subDays($i));
            if (!$isAdmin) {
                $query->whereHas('invoice', function ($query) use ($supermarketId) {
                    $query->where('supermarket_id', $supermarketId);
                });
            }
            $data[] = $query->count();
        }
        return $data;
    }

    protected function getDailyCustomersChart(bool $isAdmin, ?int $supermarketId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $query = Customer::whereDate('created_at', Carbon::today()->subDays($i));
            if (!$isAdmin) {
                $query->whereHas('invoices', function ($query) use ($supermarketId) {
                    $query->where('supermarket_id', $supermarketId);
                });
            }
            $data[] = $query->count();
        }
        return $data;
    }
}
