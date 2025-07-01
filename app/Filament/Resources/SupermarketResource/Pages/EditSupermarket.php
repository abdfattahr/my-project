<?php

namespace App\Filament\Resources\SupermarketResource\Pages;

use App\Filament\Resources\SupermarketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupermarket extends EditRecord
{
    protected static string $resource = SupermarketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
