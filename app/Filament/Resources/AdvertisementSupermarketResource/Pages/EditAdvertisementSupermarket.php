<?php

namespace App\Filament\Resources\AdvertisementSupermarketResource\Pages;

use App\Filament\Resources\AdvertisementSupermarketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisementSupermarket extends EditRecord
{
    protected static string $resource = AdvertisementSupermarketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
