<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcategorieResource\Pages;
use App\Filament\Resources\SubcategorieResource\RelationManagers\ProductsRelationManager;
use App\Models\Subcategorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class SubcategorieResource extends Resource
{
    protected static ?string $model = Subcategorie::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationLabel = 'الأقسام الفرعية';

    protected static ?string $navigationGroup = 'إدارة المتجر';

    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم القسم الفرعي')
                                    ->required()
                                    ->maxLength(80)
                                    ->rules(['string', 'min:3', 'max:80', 'regex:/^[\p{L}\s]+$/u'])
                                    ->validationMessages([
                                        'required' => 'اسم القسم الفرعي مطلوب.',
                                        'string' => 'يجب أن يكون اسم القسم الفرعي نصًا.',
                                        'min' => 'يجب أن يحتوي اسم القسم الفرعي على 3 أحرف على الأقل.',
                                        'max' => 'يجب ألا يتجاوز اسم القسم الفرعي 80 حرفًا.',
                                        'regex' => 'اسم القسم الفرعي يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات غير مسموح بها).',
                                    ]),

                                Forms\Components\Select::make('main_category_id')
                                    ->label('القسم الرئيسي')
                                    ->relationship('mainCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null)
                                    ->dehydrated(fn ($record) => $record === null)
                                    ->rules(['exists:main_categories,id'])
                                    ->validationMessages([
                                        'required' => 'القسم الرئيسي مطلوب.',
                                        'exists' => 'القسم الرئيسي المختار غير موجود.',
                                    ]),

                                Forms\Components\FileUpload::make('icon')
                                    ->label('الأيقونة')
                                    ->image()
                                    ->directory('icons/subcategories')
                                    ->maxSize(1024)
                                    ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth('64')
                                    ->imageResizeTargetHeight('64')
                                    ->nullable()
                                    ->afterStateUpdated(function ($state, $record) {
                                        if ($record && $record->icon && $state) {
                                            \Illuminate\Support\Facades\Storage::disk('public')->delete($record->icon);
                                        }
                                    })
                                    ->rules(['nullable', 'file', 'mimes:png,svg', 'max:1024'])
                                    ->validationMessages([
                                        'file' => 'يجب أن يكون الملف المرفوع صورة.',
                                        'mimes' => 'يجب أن تكون الأيقونة بصيغة PNG أو SVG.',
                                        'max' => 'يجب ألا يتجاوز حجم الأيقونة 1 ميجابايت.',
                                    ]),
                            ])
                            ->columnSpan(2),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('الأيقونة')
                    ->defaultImageUrl(url('icons/folder.png'))
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القسم الفرعي')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mainCategory.name')
                    ->label('القسم الرئيسي')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('أنشئ في')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('main_category_id')
                    ->label('القسم الرئيسي')
                    ->relationship('mainCategory', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض المنتجات')
                    ->url(fn ($record) => route('filament.admin.resources.products.index',
                    ['tableFilters[subcategory_id][value]' => $record->id,])),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->before(function ($record) {
                        if ($record->icon) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($record->icon);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                        ->visible(fn () => auth()->user()->hasRole('admin'))
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->icon) {
                                    \Illuminate\Support\Facades\Storage::disk('public')->delete($record->icon);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubcategories::route('/'),
            'create' => Pages\CreateSubcategorie::route('/create'),
            'edit' => Pages\EditSubcategorie::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin') || (auth()->user()->hasRole('vendor') && auth()->user()->id === $record->mainCategory->user_id);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor']);
    }

    public static function getLabel(): string
    {
        return 'قسم فرعي';
    }

    public static function getPluralLabel(): string
    {
        return 'الأقسام الفرعية';
    }
}
