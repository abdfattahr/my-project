<?php

namespace App\Filament\Resources\MainCategorieResource\Pages;

use App\Filament\Resources\MainCategorieResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMainCategorie extends CreateRecord
{
    protected static string $resource = MainCategorieResource::class;

    protected function afterCreate(): void
    {
        // إعادة توجيه المستخدم إلى صفحة الأقسام بعد الإنشاء
        $this->redirect(MainCategorieResource::getUrl('index'));
    }
}