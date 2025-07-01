<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket;
            if ($supermarket) {
                $this->record->supermarkets()->sync([$supermarket->id]);
            }
        }
    }
}