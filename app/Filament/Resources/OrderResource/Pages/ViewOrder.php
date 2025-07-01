<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()
                ->label('تعديل')
                ->icon('heroicon-o-pencil')
                ->color('primary'),
            \Filament\Actions\DeleteAction::make()
                ->label('حذف')
                ->icon('heroicon-o-trash')
                ->color('danger'),
            Action::make('print')
                ->label('طباعة طلب')
                ->icon('heroicon-o-printer')
                ->color('primary')
                    // JavaScript للطباعة
                    ->extraAttributes(['onclick' => 'window.print()'])
            ->requiresConfirmation(false)
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('تفاصيل الطلب')
                    ->icon('heroicon-o-shopping-cart')
                    ->headerActions([
                        \Filament\Infolists\Components\Actions\Action::make('back')
                            ->label('رجوع')
                            ->icon('heroicon-o-arrow-left')
                            ->url(fn () => OrderResource::getUrl('index'))
                            ->color('gray'),
                    ])
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('رقم الطلب')
                            ->icon('heroicon-o-identification')
                            ->color('primary')
                            ->weight('bold')
                            ->extraAttributes(['class' => 'text-lg']),

                        Infolists\Components\TextEntry::make('unit_price')
                            ->label('سعر الوحدة')
                            ->icon('heroicon-o-currency-dollar')
                            ->color('success')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ل.س')
                            ->weight('medium')
                            ->extraAttributes(['class' => 'text-green-600']),

                        Infolists\Components\TextEntry::make('date_order')
                            ->label('تاريخ الطلب')
                            ->icon('heroicon-o-calendar')
                            ->color('info')
                            ->date()
                            ->weight('medium'),

                        Infolists\Components\TextEntry::make('invoice.id')
                            ->label('رقم الفاتورة')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->weight('bold'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->extraAttributes([
                        'class' => 'bg-gray-50 shadow-sm rounded-lg border border-gray-200',
                    ]),

                Infolists\Components\Section::make('تفاصيل المنتج والكمية')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('اسم المنتج')
                            ->icon('heroicon-o-tag')
                            ->color('purple')
                            ->default('غير متوفر')
                            ->weight('medium'),

                        Infolists\Components\TextEntry::make('amount')
                            ->label('الكمية')
                            ->icon('heroicon-o-shopping-bag')
                            ->color('warning')
                            ->weight('medium'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->extraAttributes([
                        'class' => 'bg-blue-50 shadow-sm rounded-lg border border-blue-200',
                    ]),

                Infolists\Components\Section::make('معلومات إضافية')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('location')
                            ->label('الموقع')
                            ->icon('heroicon-o-map-pin')
                            ->color('danger')
                            ->weight('medium')
                            ->formatStateUsing(fn ($state) => OrderResource::getAddressFromCoordinates($state)),

                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->colors([
                                'pending' => 'warning',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                            ])
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending' => 'معلق',
                                'accepted' => 'مقبول',
                                'rejected' => 'مرفوض',
                                default => 'غير معروف',
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->icon('heroicon-o-clock')
                            ->color('gray')
                            ->dateTime()
                            ->weight('medium'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->extraAttributes([
                        'class' => 'bg-green-50 shadow-sm rounded-lg border border-green-200',
                    ]),
            ]);
    }
}
