<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupermarktProductResource\Pages;
use App\Models\SupermarktProduct;
use App\Models\Supermarket;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupermarktProductResource extends Resource
{
    protected static ?string $model = SupermarktProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'كمية المنتجات';
    protected static ?string $navigationGroup = 'إدارة المتجر';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('كمية المنتج')
                    ->schema([
                        Forms\Components\Select::make('supermarket_id')
                            ->label('السوبر ماركت')
                            ->options(function () {
                                if (auth()->user()->hasRole('admin')) {
                                    // الـ Admin يرى جميع المتاجر
                                    $supermarkets = Supermarket::query()->pluck('name', 'id');
                                } else {
                                    // التاجر يرى متجره فقط
                                    $supermarket = auth()->user()->supermarket;
                                    $supermarkets = $supermarket ? collect([$supermarket->id => $supermarket->name]) : collect();
                                }
                                return $supermarkets->isEmpty() ? ['' => 'لا توجد متاجر متاحة'] : $supermarkets;


                            })

                            ->searchable()
                            ->required()
                            ->noSearchResultsMessage('لا توجد متاجر متاحة')
                            ->disabled(fn () => !auth()->user()->hasRole('admin') && !auth()->user()->supermarket)
                            ->default(fn () => auth()->user()->hasRole('vendor') && auth()->user()->supermarket ? auth()->user()->supermarket->id : null)
                            ->helperText(fn () => !auth()->user()->hasRole('admin') && !auth()->user()->supermarket ? 'يجب أن يكون لديك متجر مرتبط.' : ''),
                        Forms\Components\TextInput::make('stock')
                            ->label('المخزون')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                            Forms\Components\Select::make('product_id')
                            ->label('المنتج')
                            ->options(function () {
                                $products = Product::query()->pluck('name', 'id');
                                return $products->isEmpty() ? ['' => 'لا توجد منتجات متاحة'] : $products;
                            })
                            ->searchable()
                            ->required()
                            ->noSearchResultsMessage('لا توجد منتجات متاحة')
                            ->disabled(fn () => Product::count() === 0)
                            ->helperText(fn () => Product::count() === 0 ? 'يجب إضافة منتج أولاً من قسم المنتجات.' : ''),

                    ])->columns(2),
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

                Tables\Columns\ImageColumn::make('product.image')
                ->label('صورة المنتج')
                ->circular()
                    ->size(70)
                    ->disk('public'),
                Tables\Columns\TextColumn::make('product.name')
                ->label('المنتج')
                ->searchable(),
                Tables\Columns\TextColumn::make('supermarket.name')
                    ->label('السوبر ماركت')
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('admin')), // إخفاء العمود للتاجر
                Tables\Columns\TextColumn::make('stock')
                    ->label('المخزون'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supermarket_id')
                    ->label('السوبر ماركت')
                    ->relationship('supermarket', 'name')
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف'),
                ]),
            ])
            ->emptyStateHeading('لا توجد منتجات مرتبطة بالسوبر ماركت بعد')
            ->emptyStateDescription('اضغط على "إنشاء" لإضافة منتج جديد.');
    }

    public static function getRelations(): array
    {
        return [
            // لا حاجة لمدير علاقات هنا
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupermarktProducts::route('/'),
            'create' => Pages\CreateSupermarktProduct::route('/create'),
            'edit' => Pages\EditSupermarktProduct::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('كمية منتج');
    }

    public static function getPluralLabel(): string
    {
        return __('🛒 كمية المنتجات');
    }

    // التحكم في الصلاحيات
    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'vendor']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'vendor']);
    }

    public static function canEdit($record): bool
    {
        if (auth()->user()->hasRole('admin')) {
            return true; // الـ Admin يمكنه تعديل أي ربط
        }
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket;
            return $supermarket && $record->supermarket_id === $supermarket->id; // التاجر يمكنه تعديل ربط متجره فقط
        }
        return false;
    }

    public static function canDelete($record): bool
    {
        if (auth()->user()->hasRole('admin')) {
            return true; // الـ Admin يمكنه حذف أي ربط
        }
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket;
            return $supermarket && $record->supermarket_id === $supermarket->id; // التاجر يمكنه حذف ربط متجره فقط
        }
        return false;
    }
}
