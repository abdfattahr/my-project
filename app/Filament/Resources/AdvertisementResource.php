<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\AdvertisementResource\RelationManagers\SupermarketsRelationManager;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as ComponentsSection;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'الإعلانات';
    protected static ?string $navigationGroup = 'إدارة الإعلانات';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsSection::make('مواصفات')
                    ->description('صف اعلان متجرك هنا😊')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('description')
                        ->label('وصف الإعلان')
                        ->maxLength(200)
                        ->required()
                        ->rules(['string', 'min:4', 'max:200'])
                        ->validationMessages([
                            'required' => 'وصف الإعلان مطلوب.',
                            'string' => 'يجب أن يكون الوصف نصًا.',
                            'min' => 'يجب أن يحتوي الوصف على 10 أحرف على الأقل.',
                            'max' => 'يجب ألا يتجاوز الوصف 200 حرف.',
                            ])
                            ->afterStateUpdated(function ($state, $record) {
                                if ($record && $state !== $record->description) {
                                    $record->update(['description' => $state]);
                                }
                                }),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة الإعلان')
                            ->disk('public')
                            ->directory('images/advertisements')
                            ->nullable()
                            ->imageEditor()
                            ->image()
                            ->rules(['nullable'])
                            ->validationMessages([
                                'image' => 'يجب أن يكون الملف صورة.',
                            ]),

                        // حقل اختيار المتجر (مرئي فقط للأدمن)
                        Forms\Components\Select::make('supermarket_id')
                            ->label('المتجر')
                            ->options(\App\Models\Supermarket::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->required(fn () => auth()->user()->hasRole('admin'))
                            ->rules(['nullable', 'exists:supermarkets,id'])
                            ->validationMessages([
                                'required' => 'يجب اختيار متجر للإعلان.',
                                'exists' => 'المتجر المختار غير موجود.',
                            ])
                            ->saveRelationshipsUsing(function ($record, $state) {
                                if (auth()->user()->hasRole('admin') && $state) {
                                    $existing = \App\Models\Advertisement_Supermarket::where('advertisement_id', $record->id)
                                        ->where('supermarket_id', $state)
                                        ->first();

                                    if (!$existing) {
                                        \App\Models\Advertisement_Supermarket::create([
                                            'advertisement_id' => $record->id,
                                            'supermarket_id' => $state,
                                            'date_publication' => now(),
                                        ]);
                                    }
                                }
                            }),
                    ])->columns(2),
            ]);
    }

 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('وصف الإعلان')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('صورة الإعلان')
                    ->size(70)
                    ->disk('public'),

                Tables\Columns\TextColumn::make('supermarkets.name')
                    ->label('المتجر')
                    ->searchable()
                    ->default('-')
                    ->visible(fn () => auth()->user()->hasRole('admin')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supermarkets')
                    ->label('المتجر')
                    ->relationship('supermarkets', 'name')
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(' عرض اعلان'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                        ->visible(fn () => auth()->user()->hasRole('admin')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SupermarketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('اعلان');
    }

    public static function getPluralLabel(): string
    {
        return __('📣الاعلانات');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
