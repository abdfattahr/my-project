<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        
            if (auth()->user()->hasRole('vendor')) {
                $supermarket = auth()->user()->supermarket;
                if ($supermarket) {
                    $this->record->supermarkets()->attach($supermarket->id);
                }
            }
        
    }
}