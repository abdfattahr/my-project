<?php

namespace App\Filament\Resources\CustomerFavoriteResource\Pages;

use App\Filament\Resources\CustomerFavoriteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerFavorites extends ListRecords
{
    protected static string $resource = CustomerFavoriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
