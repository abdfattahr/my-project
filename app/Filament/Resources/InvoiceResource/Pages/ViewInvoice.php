<?php

namespace App\Filament\Resources\InvoiceResource\Pages;
use Filament\Infolists;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action; // استيراد فئة Action
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;


class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?string $title = 'عرض تفاصيل الفاتورة';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
            ->label('طباعة الفاتورة')
            ->icon('heroicon-o-printer')
            ->color('primary')
            ->extraAttributes(['onclick' => 'window.print()'])
            ->requiresConfirmation(false),];
    }

    public  function infolist(Infolist $infolist): Infolist
    {
        return $infolist

        ->schema([
            Infolists\Components\Section::make('تفاصيل الفاتورة')
                ->icon('heroicon-s-clipboard-document-list')
                ->headerActions([
                    \Filament\Infolists\Components\Actions\Action::make('back')
                        ->label('رجوع')
                        ->icon('heroicon-o-arrow-left')
                        ->url(fn () => InvoiceResource::getUrl('index'))
                        ->color('gray'),
                ])
                ->schema([
                        Infolists\Components\TextEntry::make('id')
                        ->label('رقم الفاتورة')
                        ->icon('heroicon-o-identification')
                        ->color('primary')
                        ->weight('bold')
                        ->extraAttributes(['class' => 'text-lg'])
                        ,
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('إجمالي السعر')
                            ->icon('heroicon-o-currency-dollar')
                            ->color('success')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ل.س'),
                        TextEntry::make('information')->label('المعلومات'),
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
                            'cancelled' => 'مرفوض',
                            default => 'غير معروف',
                        }),
                        TextEntry::make('payment_method')
                            ->label('طريقة الدفع')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'cash' => 'success',
                                'points' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'cash' => 'كاش',
                                'points' => 'نقاط',
                                default => 'غير معروف',
                            }),
                        TextEntry::make('supermarket.name')->label('اسم المتجر'),
                        TextEntry::make('customer.name')->label('اسم الزبون'),
                        Infolists\Components\TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->icon('heroicon-o-calendar')
                        ->color('info')
                        ->date()
                        ->weight('medium'),


                    ])
                    ->collapsible()
                    ->columns(2)
                    ->extraAttributes([
                        'class' => 'bg-gray-50 shadow-sm rounded-lg border border-gray-200',
                    ]),
            ]);
    }

}

