<?php

namespace App\Filament\Resources\TradeMarkResource\Pages;

use App\Filament\Resources\TradeMarkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTradeMarks extends ListRecords
{
    protected static string $resource = TradeMarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
