<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerFavoriteResource\Pages;
use App\Filament\Resources\CustomerFavoriteResource\RelationManagers;
use App\Models\Customer_Favorites;
use App\Models\CustomerFavorite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CustomerFavoriteResource extends Resource
{
    protected static ?string $model = Customer_Favorites::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'المفضلات';
    protected static ?string $navigationGroup = 'إدارة عامة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('تفاصيل المفضلة')
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('العميل')
                                    ->relationship('customer', 'name') // يفترض أن لديك حقل 'name' في جدول customers
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => !Auth::check() || Auth::user()->role !== 'admin'),

                                Forms\Components\Select::make('product_id')
                                    ->label('المنتج')
                                    ->relationship('product', 'name') // يفترض أن لديك حقل 'name' في جدول products
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => !Auth::check() || Auth::user()->role !== 'admin'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true)
                                    ->disabled(fn () => !Auth::check() || Auth::user()->role !== 'admin'),

                                Forms\Components\Textarea::make('notes')
                                    ->label('ملاحظات')
                                    ->maxLength(500)
                                    ->nullable()
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('العميل')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerFavorites::route('/'),
            'create' => Pages\CreateCustomerFavorite::route('/create'),
            'edit' => Pages\EditCustomerFavorite::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

     public static function canEdit($record): bool
     {
        return false;
     }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getLabel(): string
    {
        return __('مفضلة');
    }

    public static function getPluralLabel(): string
    {
        return 'المفضلات ❤ ';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

}
