<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use Filament\Tables\Actions\EditAction;


class RecentOrders extends TableWidget
{
    protected static ?string $heading = 'أحدث الطلبات'; // إضافة عنوان للويدجت
    protected static ?int $sort = 5; // ترتيب الويدجت في لوحة التحكم

    protected int | string | array $columnSpan = 'full'; // جعل الويدجت يأخذ العرض الكامل

    protected function getTableQuery(): ?Builder
    {
        return Order::query()
            ->when(
                auth()->user()->hasRole('vendor') && !auth()->user()->hasRole('admin'),
                fn ($query) => $query->whereHas('invoice', fn ($q) => $q->where('supermarket_id', auth()->user()->supermarket?->id))
            )
            ->with(['invoice.supermarket', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('رقم الطلب'),
            Tables\Columns\TextColumn::make('unit_price')
                ->label('سعر الوحدة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ل.س'),
            Tables\Columns\TextColumn::make('date_order')
                ->label('تاريخ الطلب')
                ->searchable()
                ->date(),
            Tables\Columns\TextColumn::make('invoice.id')
                ->label('رقم الفاتورة')
                ->searchable()
                ,
            Tables\Columns\TextColumn::make('invoice.supermarket.name')
                ->label('اسم المتجر')
                ->searchable()

                ->default('غير متوفر')
                ->visible(fn () => auth()->user()->hasRole('admin')), // يظهر فقط للمسؤول
            Tables\Columns\TextColumn::make('product.name')
                ->label('اسم المنتج')
                ->searchable()

                ->default('غير متوفر'),
            Tables\Columns\TextColumn::make('amount')
                ->label('الكمية'),
            Tables\Columns\TextColumn::make('location')
                ->label('الموقع')
                ->searchable()
                ->formatStateUsing(fn ($state) => static::getAddressFromCoordinates($state)),
                Tables\Columns\TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'pending' => 'warning',
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn ($state) => match ($state) {
                    'pending' => 'معلق',
                    'accepted' => 'مقبول',
                    'rejected' => 'مرفوض',
                    default => 'غير معروف',
                }),            Tables\Columns\TextColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime(),
        ];
    }
    public static function getAddressFromCoordinates($coordinates)
    {
        try {
            $client = new Client();
            [$lat, $lon] = explode(',', $coordinates);

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                    'addressdetails' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'YourAppName/1.0',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['display_name'] ?? 'غير متوفر';
        } catch (\Exception $e) {
            return 'خطأ في استرجاع الموقع: ' . $e->getMessage();
        }
    }


    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('تعديل')
                ->url(fn ($record) => OrderResource::getUrl('edit', ['record' => $record]))
                ->visible(fn ($record) => auth()->user()->hasRole('admin') || auth()->user()->id === $record->user_id),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'vendor']);
    }
}
