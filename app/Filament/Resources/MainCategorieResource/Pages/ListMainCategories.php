<?php

namespace App\Filament\Resources\MainCategorieResource\Pages;

use App\Filament\Resources\MainCategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMainCategories extends ListRecords
{
    protected static string $resource = MainCategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
