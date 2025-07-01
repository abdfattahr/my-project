<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Filament\Resources\ProductReviewResource\RelationManagers;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'تقييمات المنتجات';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'إدارة عامة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('تفاصيل التقييم')
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('العميل')
                                    ->relationship('customer', 'name') // يفترض أن لديك حقل 'name' في جدول customers
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => !Auth::check() || Auth::user()->role !== 'admin'), // تعطيل التعديل لغير المسؤولين

                                Forms\Components\Select::make('product_id')
                                    ->label('المنتج')
                                    ->relationship('product', 'name') // يفترض أن لديك حقل 'name' في جدول products
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn () => !Auth::check() || Auth::user()->role !== 'admin'), // تعطيل التعديل لغير المسؤولين

                                Forms\Components\Select::make('rating')
                                    ->label('التقييم')
                                    ->options([
                                        1 => '1 نجمة',
                                        2 => '2 نجوم',
                                        3 => '3 نجوم',
                                        4 => '4 نجوم',
                                        5 => '5 نجوم',
                                    ])
                                    ->required()
                                    ->default(5)
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

                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn (int $state): string => "{$state} " . ($state == 1 ? 'نجمة' : 'نجوم'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        1 => '1 نجمة',
                        2 => '2 نجوم',
                        3 => '3 نجوم',
                        4 => '4 نجوم',
                        5 => '5 نجوم',
                    ]),
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
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }


    public static function canViewAny(): bool
    {
        return false;
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
        return __('تقييم منتج');
    }

    public static function getPluralLabel(): string
    {
        return '⭐ تقييمات المنتجات';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

}
