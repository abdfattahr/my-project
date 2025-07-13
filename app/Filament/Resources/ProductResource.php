<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Subcategorie;
use App\Models\TradeMark;
use App\Models\Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as ComponentsSection;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'المنتجات';

    protected static ?string $navigationGroup = 'إدارة المتجر';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';


public static function getNavigationBadge(): ?string
{
    $count = static::getModel()::query()
        ->when(auth()->user()->hasRole('vendor'), function (Builder $query) {
            $supermarket = auth()->user()->supermarket;
            if ($supermarket) {
                $query->whereHas('supermarkets', function (Builder $q) use ($supermarket) {
                    $q->where('supermarkets.id', $supermarket->id);
                });
            } else {
                $query->whereRaw('0 = 1'); // إذا ما فيش متجر مرتبط، يرجع 0
            }
        })
        ->count();

    return $count > 0 ? (string) $count : null; // يرجع العدد كسلسلة إذا موجود، أو null إذا 0
}
    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                ComponentsSection::make('المنتج')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم المنتج')
                        ->required()
                        ->maxLength(40)
                        ->rules(['string', 'min:3', 'max:40', 'regex:/^[\p{L}\s]+$/u'])
                        ->validationMessages([
                            'required' => 'اسم المنتج مطلوب.',
                            'string' => 'يجب أن يكون اسم المنتج نصًا.',
                            'min' => 'يجب أن يحتوي اسم المنتج على 3 أحرف على الأقل.',
                            'max' => 'يجب ألا يتجاوز اسم المنتج 40 حرفًا.',
                            'regex' => 'اسم المنتج يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات السفلية غير مسموح بها).',
                        ]),

                    Forms\Components\TextInput::make('price')
                        ->label('السعر')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix('ل.س'),
                ])->columns(3),

                ComponentsSection::make('الربط')
                    ->description('اربط العلامة التجارية والقسم الفرعي مع المنتج')
                    ->schema([
                        Forms\Components\Select::make('trade_mark_id')
                            ->label('العلامة التجارية')
                            ->relationship('tradeMark', 'name')
                            ->options(fn () => TradeMark::pluck('name', 'id')->all())
                            ->searchable()
                            ->required()
                            ->rules(['required', 'exists:trade_marks,id'])
                            ->validationMessages([
                                'required' => 'العلامة التجارية مطلوبة.',
                                'exists' => 'العلامة التجارية المختارة غير موجودة.',
                            ])
                            ->preload(),

                        Forms\Components\Select::make('subcategory_id')
                            ->label('القسم الفرعي')
                            ->relationship('subcategory', 'name')
                            ->options(fn () => Subcategorie::pluck('name', 'id')->all())
                            ->searchable()
                            ->required()
                            ->rules(['required', 'exists:subcategories,id'])
                            ->validationMessages([
                                'required' => 'القسم الفرعي مطلوب.',
                                'exists' => 'القسم الفرعي المختار غير موجود.',
                            ])
                            ->preload(),

                        Forms\Components\Select::make('supermarket_id')
                            ->label('المتجر')
                            ->relationship('supermarkets', 'name')
                            ->options(fn () => Supermarket::pluck('name', 'id')->all())
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->required(fn () => auth()->user()->hasRole('admin'))
                            ->rules(['array'])
                            ->validationMessages([
                                'required' => 'يجب اختيار متجر واحد على الأقل.',
                                'array' => 'يجب أن تكون القيمة مصفوفة.',
                            ])
                            ->visible(fn () => auth()->user()->hasRole('admin')),
                    ])->columns(2),

                ComponentsSection::make('مواصفات')->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('صورة المنتج')
                        ->image()
                        ->imageEditor()
                        ->required()
                        ->disk('public')
                        ->directory('products')
                        ->rules(['nullable'])
                        ->nullable(),
                    Forms\Components\MarkdownEditor::make('description')
                        ->label('وصف المنتج')
                        ->nullable()
                        ->required()
                        ->extraAttributes(['style' => 'height: 150px;'])
                        ->rules(['string', 'min:3', 'max:40', 'regex:/[\p{L}\s]+$/u'])
                        ->validationMessages([
                            'required' => 'وصف المنتج مطلوب.',
                            'string' => 'يجب أن يكون وصف المنتج نصًا.',
                            'min' => 'يجب أن يحتوي وصف المنتج على 3 أحرف على الأقل.',
                            'max' => 'يجب ألا يتجاوز وصف المنتج 40 حرفًا.',
                            'regex' => 'وصف المنتج يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات السفلية غير مسموح بها).',
                        ]),
                ])->columnSpanFull()->columns(2),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('vendor')) {
                    $supermarket = auth()->user()->supermarket;
                    if ($supermarket) {
                        $query->whereHas('supermarkets', function (Builder $q) use ($supermarket) {
                            $q->where('supermarkets.id', $supermarket->id);
                        });
                    }
                    else {
                        $query->whereRaw('0 = 1');
                    }
                }
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('صورة المنتج')
                    ->circular()
                    ->size(100)
                    ->disk('public'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('SYP', locale: 'ar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tradeMark.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('القسم الفرعي')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supermarkets.name')
                    ->label('المتجر')
                    ->formatStateUsing(fn ($record) => $record->supermarkets->pluck('name')->implode(', '))
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trade_mark_id')
                    ->label('العلامة التجارية')
                    ->relationship('tradeMark', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('subcategory_id')
                    ->label('الفئة الفرعية')
                    ->relationship('subcategory', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('supermarkets')
                    ->label('المتجر')
                    ->relationship('supermarkets', 'name')
                    ->multiple()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
                Tables\Actions\ViewAction::make()->label('عرض منتج'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد منتجات بعد')
            ->emptyStateDescription('اضغط على "إنشاء" لإضافة منتج جديد.')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SupermarketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateProduct::route('/create'),
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'منتج';
    }

    public static function getPluralLabel(): ?string
    {
        return '🛒 المنتجات';
    }

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
            return true;
        }
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket;
            return $supermarket && $record->supermarkets->contains($supermarket->id);
        }
        return false;
    }

    public static function canDelete($record): bool
    {
        if (auth()->user()->hasRole('admin')) {
            return true;
        }
        if (auth()->user()->hasRole('vendor')) {
            $supermarket = auth()->user()->supermarket;
            return $supermarket && $record->supermarkets->contains($supermarket->id);
        }
        return false;
    }
}
