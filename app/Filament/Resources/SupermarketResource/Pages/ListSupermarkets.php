<?php

namespace App\Filament\Resources\SupermarketResource\Pages;

use App\Filament\Resources\SupermarketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupermarkets extends ListRecords
{
    protected static string $resource = SupermarketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
