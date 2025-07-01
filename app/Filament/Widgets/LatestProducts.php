<?php

namespace App\Filament\Widgets;

use App\Models\SupermarktProduct;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Forms;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestProducts extends TableWidget
{
    protected static ?string $heading = 'أحدث المنتجات';
     // استخدام $heading بدلاً من $title
    protected static ?int $sort = 5; // ترتيب الويدجت في لوحة التحكم
    protected int | string | array $columnSpan = 'full'; // جعل الويدجت يأخذ العرض الكامل
     // ترتيب الويدجت في لوحة التحكم

    protected function getTableQuery(): Builder
    {
        $query = SupermarktProduct::query()
            ->with(['supermarket', 'product']) // تحميل العلاقات لتجنب مشاكل N+1
            ->latest()
            ->take(5);
        // إذا كان المستخدم تاجرًا (Vendor)، قم بتصفية المنتجات بناءً على متجره
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket; // استخدام العلاقة supermarket
            if ($supermarket) {
                $query->where('supermarket_id', $supermarket->id);
            } else {
                $query->whereRaw('0 = 1');
                // منع رؤية أي منتجات إذا لم يكن للتاجر متجر
            }
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [

            ImageColumn::make('product.image')
                ->label(__('صورة المنتج'))
                ->disk('public')
                ->size(50)
                ->circular()
                ->defaultImageUrl(url('images/default-product.png')),

            TextColumn::make('supermarket.name')
                ->label(__('السوبر ماركت'))
                ->searchable()
                ->sortable()
                ->visible(fn () => auth()->user()->hasRole('admin')),
            TextColumn::make('product.name')
                ->label(__('المنتج'))
                ->searchable()
                ->sortable(),
            TextColumn::make('stock')
                ->label(__('المخزون'))
                ->sortable(),
            TextColumn::make('product.price')
                ->label(__(' السعر '))
                ->money('SYP', locale: 'ar')
                ->sortable('product.price'),

        ];
    }

    protected function getTableActions(): array
    {
        return [
            //!  للتعديل
            EditAction::make()
                ->label('تعديل')
                ->form([
                    Forms\Components\TextInput::make('stock')
                        ->label('المخزون')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('product.price')
                        ->label('تعديل السعر')
                        ->nullable()
                        ->helperText('اتركه فارغًا لاستخدام السعر الأصلي للمنتج.')
                        ->suffix('ل.س'),

                ])
                ->modalHeading('تعديل المنتج')
                ->modalButton('حفظ التعديلات') // نص زر الحفظ في Filament v2
                ->authorize(function (SupermarktProduct $record) {
                    if (auth()->user()->hasRole('admin')) {
                        return true;
                         //! الـ Admin يمكنه تعديل كل شيء
                    }
                    if (auth()->user()->hasRole('vendor')) {
                        $supermarket = auth()->user()->supermarket;
                        return $supermarket && $record->supermarket_id === $supermarket->id;
                        //! التاجر يعدل منتجات متجره فقط
                    }
                    return false;
                }),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor'])
        ; // السماح للأدمن والتاجر
    }
}
