<?php

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Vendor\Widgets\VendorStats;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
        ];
    }
}
