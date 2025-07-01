<?php

namespace App\Filament\Resources\MainCategorieResource\Pages;

use App\Filament\Resources\MainCategorieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMainCategorie extends EditRecord
{
    protected static string $resource = MainCategorieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
