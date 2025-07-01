<?php

namespace App\Filament\Resources\AdvertisementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Supermarket;

class SupermarketsRelationManager extends RelationManager
{
    protected static string $relationship = 'supermarkets';
    protected static ?string $title = 'ربط مع المتاجر 🏪';

    public static function getLabel(): string
    {
        return __('متجر');
    }

    public static function getPluralLabel(): string
    {
        return __('ربط مع المتاجر 🏪');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('supermarket_name')
                    ->label('اسم المتجر')
                    ->default(fn ($record) => $record->name ?? '-')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\DatePicker::make('pivot.date_publication')
                    ->label('تاريخ النشر')
                    ->required()
                    ->default(now())
                    ->rules(['date', 'after_or_equal:today'])
                    ->validationMessages([
                        'required' => 'تاريخ النشر مطلوب.',
                        'date' => 'يجب أن يكون تاريخ النشر صالحًا.',
                        'after_or_equal' => 'يجب أن يكون تاريخ النشر اليوم أو في المستقبل.',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المتجر')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.date_publication')
                    ->label('تاريخ النشر')
                    ->date(),
                Tables\Columns\TextColumn::make('description')
                    ->label('وصف الإعلان')
                    ->limit(50)
                    ->default(fn () => $this->getOwnerRecord()->description ?? 'غير متوفر')
                                ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('ربط متجر')
                    ->form(fn ($action) => [
                        Forms\Components\Select::make('recordId')
                            ->label('المتجر')
                            ->options(Supermarket::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('date_publication')
                            ->label('تاريخ النشر')
                            ->required()
                            ->default(now())
                            ->rules(['date', 'after_or_equal:today']),
                    ])
                    ->using(function (RelationManager $livewire, array $data) {
                        $livewire->getOwnerRecord()->supermarkets()->attach($data['recordId'], [
                            'date_publication' => $data['date_publication'],
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('إلغاء الربط'),
            ]);
    }
}
