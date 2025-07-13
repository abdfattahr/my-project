<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TradeMarkResource\Pages;
use App\Filament\Resources\TradeMarkResource\RelationManagers;
use App\Models\TradeMark;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class TradeMarkResource extends Resource
{
    protected static ?string $model = TradeMark::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationLabel = 'العلامات التجارية';

    protected static ?string $navigationGroup = 'إدارة المتجر';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('اسم العلامة التجارية')
                                ->required()
                                ->maxLength(255)

                                ->rules(['string', 'min:3', 'max:255', 'regex:/^[\p{L}\s]+$/u'])
                                ->validationMessages([
                                    'required' => 'اسم العلامة التجارية مطلوب.',
                                    'string' => 'يجب أن يكون اسم العلامة التجارية نصًا.',
                                    'min' => 'يجب أن يحتوي اسم العلامة التجارية على 3 أحرف على الأقل.',
                                    'max' => 'يجب ألا يتجاوز اسم العلامة التجارية 255 حرفًا.',
                                    'regex' => 'اسم العلامة التجارية يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات غير مسموح بها).',
                                ]),
                        ]),
                ]),
        ]);
}
    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
            // إذا كان المستخدم تاجرًا (Vendor)، قم بتصفية المنتجات بناءً على متجره فقط
            if (auth()->user()->hasRole('vendor')) {
                $supermarket = auth()->user()->supermarket;
                if ($supermarket) {
                    $query->where('supermarket_id', $supermarket->id);
                } else {
                    $query->whereRaw('0 = 1'); // منع رؤية أي منتجات إذا لم يكن للتاجر متجر
                }
            }
            // الـ Admin يمكنه رؤية جميع المنتجات بدون تصفية
        })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم العلامة التجارية')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض المنتجات')
                    ->url(fn ($record) => route('filament.admin.resources.products.index', [
                        'tableFilters[trade_mark_id][value]' => $record->id,
                    ])),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradeMarks::route('/'),
            'create' => Pages\CreateTradeMark::route('/create'),
            'edit' => Pages\EditTradeMark::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('علامة تجارية');
    }

    public static function getPluralLabel(): string
    {
        return 'العلامات التجارية';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin');
        
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->id === $record->user_id;
    }
}
