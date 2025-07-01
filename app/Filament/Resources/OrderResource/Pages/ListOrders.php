<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
    protected function afterDelete(): void
    {
        Notification::make()
            ->title('🗑️ تم حذف الطلب')
            ->danger()
            ->body('تم حذف الطلب بنجاح.')
            ->send();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderScripts(): array
    {
        return [
            'echo-orders' => asset('js/echo-orders.js'),
        ];
    }
}
