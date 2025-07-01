<?php

namespace App\Filament\Resources\DeliveryWorkerResource\Pages;

use App\Filament\Resources\DeliveryWorkerResource;
use App\Filament\Widgets\QuickContactWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryWorkers extends ListRecords
{
    protected static string $resource = DeliveryWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            QuickContactWidget::class,
        ];
    }
}
