<?php

namespace App\Filament\Resources\SubcategorieResource\Pages;

use App\Filament\Resources\SubcategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcategorie extends EditRecord
{
    protected static string $resource = SubcategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
