<?php

namespace App\Filament\Widgets;

use App\Models\Advertisement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestAdvertisements extends TableWidget
{
    protected static ?string $heading = 'أحدث الإعلانات';
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $query = Advertisement::query();

        if (!auth()->user()->hasRole('admin')) {
            $supermarketId = auth()->user()->supermarket->id ?? null;
            if ($supermarketId) {
                $query->whereHas('supermarkets', function ($query) use ($supermarketId) {
                    $query->where('supermarket_id', $supermarketId);
                });
            } 
        }

        return $query->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            ImageColumn::make('image')
                ->label('الصورة')
                ->disk('public')
                ->size(50)
                ->circular()
                ->defaultImageUrl(url('images/default-ad.png')),

            TextColumn::make('description')
                ->label('الوصف')
                ->searchable()
                ->sortable(),

            TextColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('لا توجد إعلانات');
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return __('لا توجد إعلانات متاحة حاليًا.');
    }
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'vendor']);
    }
}
