<?php

namespace App\Filament\Resources\TradeMarkResource\Pages;

use App\Filament\Resources\TradeMarkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTradeMark extends EditRecord
{
    protected static string $resource = TradeMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
