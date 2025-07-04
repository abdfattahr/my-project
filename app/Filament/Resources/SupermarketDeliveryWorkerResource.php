<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupermarketDeliveryWorkerResource\Pages;
use App\Models\Supermarket_DeliveryWorker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as ComponentsSection;

class SupermarketDeliveryWorkerResource extends Resource
{
    protected static ?string $model = Supermarket_DeliveryWorker::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'ربط عمال التوصيل بالمتاجر';

    protected static ?string $navigationGroup = 'إدارة عمال التوصيل';

    protected static ?int $navigationSort = 8; // الأقسام الفرعية تظهر أولاً

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                   ComponentsSection::make('مواصفات')
                    ->description('اربط عامل التوصيل بمتجرك هنا😊')
                    ->schema([
                Forms\Components\Select::make('supermarket_id')
                    ->label('المتجر')
                    ->options(\App\Models\Supermarket::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('delivery_worker_id')
                    ->label('عامل التوصيل')
                    ->options(\App\Models\DeliveryWorker::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('ملاحظات 🔴 ')
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
                    ])->columns(4),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supermarket.name')
                    ->label('اسم المتجر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('deliveryWorker.name')
                    ->label('اسم عامل التوصيل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('ملاحظات 🔴 ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف'),

                Tables\Columns\TextColumn::make('delivery_time')
                    ->label('وقت التوصيل')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // يمكنك إضافة فلاتر هنا إذا لزم الأمر
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupermarketDeliveryWorkers::route('/'),
            'create' => Pages\CreateSupermarketDeliveryWorker::route('/create'),
            'edit' => Pages\EditSupermarketDeliveryWorker::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('ربط عامل توصيل');
    }

    public static function getPluralLabel(): string
    {
        return __('🏍 روابط عمال التوصيل');
    }
}
