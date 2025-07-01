<?php

namespace App\Filament\Resources\CustomerFavoriteResource\Pages;

use App\Filament\Resources\CustomerFavoriteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerFavorite extends CreateRecord
{
    protected static string $resource = CustomerFavoriteResource::class;
}
