<?php

namespace App\Filament\Widgets;

use App\Models\Supermarket;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class LatestSupermarkets extends TableWidget
{
    protected static ?string $heading = 'أحدث المتاجر'; // العنوان العربي
    protected static ?int $sort = 4; // ترتيب الويدجت في لوحة التحكم

    protected int | string | array $columnSpan = 'full'; // جعل الويدجت يأخذ العرض الكامل

    protected function getTableQuery(): Builder
    {
        return Supermarket::query()
            ->with('user') // تحميل العلاقة مع المستخدم لتجنب مشاكل N+1
            ->latest()
            ->take(5); // عرض أحدث 5 متاجر
    }

    protected function getTableColumns(): array
    {
        return [
            ImageColumn::make('image') // افتراض أن حقل الصورة في جدول users هو 'image'
                ->label('صورة  المتجر')
                ->disk('public') // القرص الذي يتم تخزين الصور فيه
                ->size(50) // حجم الصورة (يمكنك تعديله)
                ->circular() // جعل الصورة دائرية (اختياري)
                ->defaultImageUrl(url('images/default-user.png')), // صورة افتراضية إذا لم تكن هناك صورة

            TextColumn::make('name')
                ->label('اسم المتجر')
                ->searchable() // إضافة قابلية البحث
                ->sortable(),  // إضافة قابلية الترتيب

            TextColumn::make('position')
                ->label('الموقع')
                ->searchable()
                ->sortable(),

            TextColumn::make('email')
                ->label('البريد الإلكتروني')
                ->searchable(),

            TextColumn::make('phone_number')
                ->label('رقم الهاتف')
                ->searchable(),
            TextColumn::make('user.name')
                ->label('المستخدم')
                ->searchable(),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin'); // السماح فقط للمسؤول برؤية الويدجت
    }
}
