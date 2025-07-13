<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryWorkerResource\RelationManagers\DeliveryWorkerSupermarketsRelationManager;
use App\Models\DeliveryWorker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\DeliveryWorkerResource\Pages\ListDeliveryWorkers;
use App\Filament\Resources\DeliveryWorkerResource\Pages\CreateDeliveryWorker;
use App\Filament\Resources\DeliveryWorkerResource\Pages\EditDeliveryWorker;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Tables\Enums\ActionsPosition;


class DeliveryWorkerResource extends Resource
{
    protected static ?string $model = DeliveryWorker::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'عمال التوصيل';

    protected static ?string $navigationGroup = 'إدارة عمال التوصيل';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsSection::make('مواصفات')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم عامل التوصيل')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(15),

                        // حقل اختيار المتجر (يتم وضعه هنا)
                        Forms\Components\Select::make('supermarket_id')
                            ->label('المتجر')
                            ->options(\App\Models\Supermarket::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->visible(fn () => auth()->user()->hasRole('admin'))
                            ->required(fn () => auth()->user()->hasRole('admin'))
                            ->default(function () {
                                // إذا كان المستخدم تاجرًا، قم بتعيين المتجر الخاص به تلقائيًا
                                if (auth()->user()->hasRole('vendor') && auth()->user()->supermarket_id) {
                                    return auth()->user()->supermarket_id;
                                }
                                return null;
                            })
                            ->rules(['nullable', 'exists:supermarkets,id'])
                            ->validationMessages([
                                'required' => 'يجب اختيار متجر لعامل التوصيل.',
                                'exists' => 'المتجر المختار غير موجود.',
                            ])
                            ->saveRelationshipsUsing(function ($record, $state) {
                                // التعامل مع الأدمن
                                if (auth()->user()->hasRole('admin') && $state) {
                                    \App\Models\Supermarket_DeliveryWorker::create([
                                        'supermarket_id' => $state,
                                        'delivery_worker_id' => $record->id,
                                        'name' => $record->name,
                                        'phone' => $record->phone,
                                        'delivery_time' => now(),
                                    ]);
                                }
                                // التعامل مع التاجر
                                if (auth()->user()->hasRole('vendor') && auth()->user()->supermarket_id) {
                                    \App\Models\Supermarket_DeliveryWorker::create([
                                        'supermarket_id' => auth()->user()->supermarket_id,
                                        'delivery_worker_id' => $record->id,
                                        'name' => $record->name,
                                        'phone' => $record->phone,
                                        'delivery_time' => now(),
                                    ]);
                                }
                            }),
                    ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // إزالة supermarket_id من البيانات لأنه ليس عمودًا في جدول delivery_workers
        unset($data['supermarket_id']);
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم عامل التوصيل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable(),

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
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ],position:ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::
                    make()
                    ->label('حذف'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DeliveryWorkerSupermarketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryWorkers::route('/'),
            'create' => CreateDeliveryWorker::route('/create'),
            'edit' => EditDeliveryWorker::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __(' عامل توصيل');
    }

    public static function getPluralLabel(): string
    {
        return __('🏍 عمال التوصيل');
    }
}
