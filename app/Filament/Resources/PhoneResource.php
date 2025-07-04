<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneResource\Pages;
use App\Models\Phone;
use App\Models\Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as ComponentsSection;

class PhoneResource extends Resource
{
    protected static ?string $model = Phone::class;

    protected static ?string $navigationLabel = 'أرقام الهواتف';

    protected static ?string $navigationGroup = 'إدارة عامة';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

        protected static ?string $recordTitleAttribute = 'phone_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsSection::make('بيانات المتجر')->schema([
                    Forms\Components\TextInput::make('phone_number')
                        ->label('رقم الهاتف')
                        ->tel()
                        ->required()
                        ->rules(['numeric'])
                        ->validationMessages([
                            'required' => 'الرقم مطلوب.',
                            'numeric' => 'يجب أن يكون الحقل رقمًا.',
                        ])
                        ->unique(ignoreRecord: true),

                    // حقل للأدمن
                    Forms\Components\Select::make('supermarket_id')
                        ->label('السوبر ماركت')
                        ->options(Supermarket::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn () => auth()->user()->hasRole('admin')),

                    // حقل مخفي للتاجر
                    Forms\Components\Hidden::make('supermarket_id')
                        ->default(function () {
                            if (auth()->user()->hasRole('vendor')) {
                                return auth()->user()->supermarket->id;
                            }
                            return null;
                        })
                        ->required()
                        ->visible(fn () => auth()->user()->hasRole('vendor')),
                ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Phone::query();
                if (auth()->user()->hasRole('vendor')) {
                    $query->where('supermarket_id', auth()->user()->supermarket->id);
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supermarket.name')
                    ->label('السوبر ماركت')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('لا توجد أرقام هواتف بعد')
            ->emptyStateDescription('اضغط على "إنشاء" لإضافة رقم هاتف جديد.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhones::route('/'),
            'create' => Pages\CreatePhone::route('/create'),
            'edit' => Pages\EditPhone::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin') || (
            auth()->user()->hasRole('vendor') && $record->supermarket->user_id === auth()->user()->id
        );
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin') || (
            auth()->user()->hasRole('vendor') && $record->supermarket->user_id === auth()->user()->id
        );
    }

    public static function getLabel(): string
    {
        return __('هاتف');
    }

    public static function getPluralLabel(): string
    {
        return 'أرقام هواتف المتجر 📱';
    }
}
