<?php

namespace App\Filament\Resources\SupermarktProductResource\Pages;

use App\Filament\Resources\SupermarktProductResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditSupermarktProduct extends EditRecord
{
    protected static string $resource = SupermarktProductResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;
        $priceOverride = $record->price_override;

        // سجل قيمة price_override للتأكد من أنها تُقرأ بشكل صحيح
        Log::info('Price Override Value: ' . $priceOverride);

        if (!is_null($priceOverride)) {
            $product = $record->product;

            // سجل للتأكد من أن المنتج موجود
            if ($product) {
                Log::info('Product Found: ' . $product->id);

                // تحديث السعر الأساسي
                $product->update([
                    'price' => $priceOverride,
                ]);

                Log::info('Product Price Updated to: ' . $priceOverride);

                \Filament\Notifications\Notification::make()
                    ->title('تم التحديث')
                    ->body('تم تحديث السعر الأساسي للمنتج بناءً على السعر المخصص.')
                    ->success()
                    ->send();
            } else {
                Log::error('Product not found for SupermarktProduct ID: ' . $record->id);

                \Filament\Notifications\Notification::make()
                    ->title('خطأ')
                    ->body('لم يتم العثور على المنتج المرتبط.')
                    ->danger()
                    ->send();
            }
        } else {
            Log::info('Price Override is null, skipping update.');
        }
    }
}