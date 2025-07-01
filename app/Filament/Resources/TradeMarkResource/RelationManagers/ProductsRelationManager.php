<?php

namespace App\Filament\Resources\TradeMarkResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager; 
use Filament\Tables;
use Filament\Tables\Table;


class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(40),

                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->required(),

                Forms\Components\FileUpload::make('image')
                    ->label('صورة المنتج')
                    ->nullable(),

                Forms\Components\Textarea::make('description')
                    ->label('وصف المنتج')
                    ->nullable(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('syp'),

                Tables\Columns\ImageColumn::make('image')
                    ->label('صورة المنتج')
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة منتج'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }
}
