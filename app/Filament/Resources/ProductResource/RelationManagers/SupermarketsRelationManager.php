<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class SupermarketsRelationManager extends RelationManager
{    protected static ?string $heading = ' ربط باالمتجر'; // استخدام $heading بدلاً من $title
    protected static ?string $title='كمية المنتج الحالي';
    protected static string $relationship = 'supermarkets';

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
        ->recordTitleAttribute('id')

            ->columns([
                Tables\Columns\ImageColumn::make('image')
                ->label('صورة المتجر')
                ->circular()
                ->size(100)
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->disk('public'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المتجر')
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                Tables\Columns\TextColumn::make('pivot.stock')
                    ->label('الكمية'),
                    Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),

            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل')
                            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء الربط'),
                ]),
            ])
            ->emptyStateHeading('لا توجد متاجر مرتبطة بهذا المنتج')
            ->emptyStateDescription('اضغط على "ربط متجر" لإضافة متجر جديد.');
    }
}
