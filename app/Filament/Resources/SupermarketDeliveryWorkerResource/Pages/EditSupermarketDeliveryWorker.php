<?php

namespace App\Filament\Resources\SupermarketDeliveryWorkerResource\Pages;

use App\Filament\Resources\SupermarketDeliveryWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupermarketDeliveryWorker extends EditRecord
{
    protected static string $resource = SupermarketDeliveryWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
