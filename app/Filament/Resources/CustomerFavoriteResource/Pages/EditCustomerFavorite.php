<?php

namespace App\Filament\Resources\CustomerFavoriteResource\Pages;

use App\Filament\Resources\CustomerFavoriteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerFavorite extends EditRecord
{
    protected static string $resource = CustomerFavoriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
