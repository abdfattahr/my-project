<?php

namespace App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\SupermarktProductResource;

use App\Filament\Resources\AdvertisementResource;
use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisement extends EditRecord

{
    protected static string $resource = AdvertisementResource::class;

    protected function afterSave(): void
    {
    
  
    $record = $this->record;
    $priceOverride = $record->price_override;

    Log::info('Price Override Value: ' . $priceOverride);

    Log::info('Product Price Updated to: ' . $priceOverride);

    \Filament\Notifications\Notification::make()
        ->title('تم التحديث')
        ->body('تم تحديث الاعلان بنجاح.')
        ->success()
        ->send();
 
}
}
