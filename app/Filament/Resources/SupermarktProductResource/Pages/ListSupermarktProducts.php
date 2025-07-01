<?php

namespace App\Filament\Resources\SupermarktProductResource\Pages;

use App\Filament\Resources\SupermarktProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupermarktProducts extends ListRecords
{
    protected static string $resource = SupermarktProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
