<?php

namespace App\Filament\Resources\SupermarketResource\Pages;

use App\Filament\Resources\SupermarketResource;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Resources\Pages\ViewRecord;

class ViewSupermarket extends ViewRecord
{
    protected static string $resource = SupermarketResource::class;

    protected static ?string $title = 'عرض تفاصيل المتجر';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('تفاصيل المتجر')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\ImageEntry::make('image')
                                    ->label('صورة المتجر')
                                    ->width(200)
                                    ->height(200)
                                    ->disk('public')
                                    ->defaultImageUrl(url('/images/default-store.png')),
                                Components\TextEntry::make('name')
                                    ->label('اسم المتجر')
                                    ->weight('bold')
                                    ->icon('heroicon-o-building-storefront')
                                    ->color('primary'),
                                Components\TextEntry::make('position')
                                    ->label('الموقع')
                                    ->icon('heroicon-o-map-pin'),
                                Components\TextEntry::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                                Components\TextEntry::make('phone_number')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                Components\TextEntry::make('user.name')
                                    ->label('اسم المستخدم')
                                    ->weight('bold')
                                    ->color('success')
                                    ->default('غير متوفر')
                                    ->icon('heroicon-o-user')

                                    ,
                                Components\TextEntry::make('created_at')
                                    ->label('تاريخ الإنشاء')
                                    ->icon('heroicon-m-calendar-date-range')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
