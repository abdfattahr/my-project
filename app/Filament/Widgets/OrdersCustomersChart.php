<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Carbon;

class OrdersCustomersChart extends ChartWidget
{
    protected static ?string $heading = 'الطلبات والعملاء';

    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $isAdmin = auth()->user()->hasRole('admin');
        $supermarketId = $isAdmin ? null : auth()->user()->supermarket->id;

        // الطلبات
        $ordersQuery = Order::selectRaw('COUNT(*) as count, MONTH(created_at) as month, YEAR(created_at) as year')
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $ordersQuery->whereHas('invoice', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }

        // العملاء (الربط عبر الفواتير)
        $customersQuery = Customer::selectRaw('COUNT(*) as count, MONTH(created_at) as month, YEAR(created_at) as year')
            ->whereBetween('created_at', [$startDate, $endDate]);
        if (!$isAdmin) {
            $customersQuery->whereHas('invoices', function ($query) use ($supermarketId) {
                $query->where('supermarket_id', $supermarketId);
            });
        }

        $orders = $ordersQuery->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::create($item->year, $item->month)->format('M Y') => $item->count];
            })->toArray();

        $customers = $customersQuery->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::create($item->year, $item->month)->format('M Y') => $item->count];
            })->toArray();

        $labels = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $labels[] = $current->format('M Y');
            $current->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'الطلبات',
                    'data' => array_map(fn($label) => $orders[$label] ?? 0, $labels),
                    'borderColor' => '#E1D716FF',
                    'fill' => false,
                ],
                [
                    'label' => 'العملاء الجدد',
                    'data' => array_map(fn($label) => $customers[$label] ?? 0, $labels),
                    'borderColor' => '#7039CFFF',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
