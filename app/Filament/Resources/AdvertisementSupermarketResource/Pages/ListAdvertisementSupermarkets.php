<?php

namespace App\Filament\Resources\AdvertisementSupermarketResource\Pages;

use App\Filament\Resources\AdvertisementSupermarketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvertisementSupermarkets extends ListRecords
{
    protected static string $resource = AdvertisementSupermarketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
