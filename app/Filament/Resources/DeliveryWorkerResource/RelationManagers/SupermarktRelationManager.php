<?php

namespace App\Filament\Resources\DeliveryWorkerResource\RelationManagers;

use App\Models\Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryWorkerSupermarketsRelationManager extends RelationManager
{
    protected static string $relationship = 'supermarkets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supermarket_id')
                    ->label('المتجر')
                    ->options(\App\Models\Supermarket::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('ملاحظات 🔴')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required()
                    ->maxLength(15),

                Forms\Components\DateTimePicker::make('delivery_time')
                    ->label('وقت التوصيل')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المتجر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pivot.name')
                    ->label('ملاحظات 🔴')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pivot.phone')
                    ->label('رقم الهاتف'),

                Tables\Columns\TextColumn::make('pivot.delivery_time')
                    ->label('وقت التوصيل')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // يمكنك إضافة فلاتر هنا إذا لزم الأمر
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('ربط متجر')
                    ->form(fn ($action) => [
                        Forms\Components\Select::make('recordId')
                            ->label('المتجر')
                            ->options(Supermarket::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->noSearchResultsMessage('لا توجد متاجر متاحة')
                            ->helperText(Supermarket::count() === 0 ? 'يجب إضافة متجر أولاً من قسم المتاجر.' : ''),
                        Forms\Components\TextInput::make('name')
                            ->label('ملاحظات 🔴')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(15),
                        Forms\Components\DateTimePicker::make('delivery_time')
                            ->label('وقت التوصيل')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->form(fn ($action) => [
                        Forms\Components\TextInput::make('supermarket_name')
                            ->label('اسم المتجر')
                            ->default($action->getRecord()->supermarket->name)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('name')
                            ->label('ملاحظات 🔴')
                            ->required()
                            ->maxLength(100)
                            ->default($action->getRecord()->pivot->name),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(15)
                            ->default($action->getRecord()->pivot->phone),
                        Forms\Components\DateTimePicker::make('delivery_time')
                            ->label('وقت التوصيل')
                            ->required()
                            ->default($action->getRecord()->pivot->delivery_time),
                    ]),
                Tables\Actions\DetachAction::make()->label('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء الربط'),
                ]),
            ]);
    }
}
