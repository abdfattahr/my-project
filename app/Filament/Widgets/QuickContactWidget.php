<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickContactWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-contact-widget';

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return ' 📲 اتصال سريع ';
    }
}