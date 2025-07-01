<?php

namespace App\Filament\Resources\SupermarketDeliveryWorkerResource\Pages;

use App\Filament\Resources\SupermarketDeliveryWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupermarketDeliveryWorkers extends ListRecords
{
    protected static string $resource = SupermarketDeliveryWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
