<?php

namespace App\Filament\Resources\DeliveryWorkerResource\Pages;

use App\Filament\Resources\DeliveryWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryWorker extends EditRecord
{
    protected static string $resource = DeliveryWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
