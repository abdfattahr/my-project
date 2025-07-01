<?php

namespace App\Filament\Resources\SupermarketResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $title=' المنتجات ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('stock')
                    ->label('الكمية')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                ->label('صورة المنتج')
                ->circular()
                ->size(100)
                ->disk('public'),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.stock')
                    ->label('الكمية'),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر ')
                    ->money('syp'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('ربط منتج')
                    ->form(fn ($action) => [
                        $action->getRecordSelect()
                            ->label('اختر منتج')
                            ->options(function () {
                                $products = Product::all()->pluck('name', 'id');
                                return $products->isEmpty() ? ['' => 'لا توجد منتجات متاحة'] : $products;
                            })
                            ->searchable()
                            ->noSearchResultsMessage('لا توجد منتجات متاحة')
                            ->disabled(fn () => Product::count() === 0)
                            ->helperText(fn () => Product::count() === 0 ? 'يجب إضافة منتج أولاً من قسم المنتجات.' : ''),
                        Forms\Components\TextInput::make('stock')
                            ->label('الكمية')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DetachAction::make()->label('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء الربط'),
                ]),
            ])
            ->emptyStateHeading('لا توجد منتجات مرتبطة بهذا المتجر')
            ->emptyStateDescription('اضغط على "ربط منتج" لإضافة منتج جديد.');
    }
}
