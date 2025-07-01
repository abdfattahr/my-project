<?php

namespace App\Filament\Resources\AdvertisementSupermarketResource\Pages;

use App\Filament\Resources\AdvertisementSupermarketResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewAdvertisementSupermarket extends ViewRecord
{
    protected static string $resource = AdvertisementSupermarketResource::class;

    protected static ?string $title = 'عرض تفاصيل الإعلان';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات أساسية')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_publication')
                            ->label('تاريخ النشر')
                            ->icon('heroicon-o-calendar')
                            ->date()
                            ->color('primary'),

                        Infolists\Components\ImageEntry::make('advertisement.image')
                            ->label('صورة الإعلان')
                            ->disk('public')
                            ->height(250)
                            ->extraAttributes(['class' => 'rounded-lg mx-auto'])
                            ->default('-'),

                        Infolists\Components\TextEntry::make('supermarket.name')
                            ->label('اسم المتجر')
                            ->icon('heroicon-o-building-storefront')
                            ->default('-')
                            ->color('primary'),
                    ])
                    ->columns(['sm' => 1, 'lg' => 2])
                    ->collapsible()
                    ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded-lg']),

                Infolists\Components\Section::make('تفاصيل الإعلان')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('advertisement.description')
                            ->label('وصف الإعلان')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->columnSpan(2)
                            ->color('primary')
                            ->extraAttributes(['class' => 'text-gray-700']),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->icon('heroicon-o-clock')
                            ->dateTime()
                            ->color('primary'),
                    ])
                    ->columns(['sm' => 1, 'lg' => 2])
                    ->collapsible()
                    ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded-lg']),
            ]);
    }
}
