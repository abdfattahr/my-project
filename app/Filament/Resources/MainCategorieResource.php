<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MainCategorieResource\Pages;
use App\Filament\Resources\MainCategorieResource\RelationManagers\SubcategoriesRelationManager;
use App\Models\MainCategorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\ImageColumn;

class MainCategorieResource extends Resource
{
    protected static ?string $model = MainCategorie::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'الأقسام الرئيسية';
    protected static ?string $navigationGroup = 'إدارة المتجر';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        {
            return $form
                ->schema([
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('اسم القسم')
                                        ->required()
                                        ->maxLength(30)
                                        ->rules(['string', 'min:3', 'max:30', 'regex:/^[\p{L}\s]+$/u'])
                                        ->validationMessages([
                                            'required' => 'اسم القسم مطلوب.',
                                            'string' => 'يجب أن يكون اسم القسم نصًا.',
                                            'min' => 'يجب أن يحتوي اسم القسم على 3 أحرف على الأقل.',
                                            'max' => 'يجب ألا يتجاوز اسم القسم 30 حرفًا.',
                                            'regex' => 'اسم القسم يجب أن يحتوي فقط على حروف ومسافات (الأرقام والشرطات غير مسموح بها).',
                                        ]),

                                    Forms\Components\FileUpload::make('icon')
                                        ->label('الأيقونة')
                                        ->image()
                                        ->directory('icons')
                                        ->maxSize(1024)
                                        ->acceptedFileTypes(['image/png', 'image/svg+xml'])
                                        ->imageResizeMode('cover')
                                        ->imageResizeTargetWidth('64')
                                        ->imageResizeTargetHeight('64')
                                        ->nullable()
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
                    ->label('اسم القسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('أنشئ في')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض الأقسام الفرعية')
                    ->url(fn ($record) => route('filament.admin.resources.subcategories.index', [
                    'tableFilters[main_category_id][value]' => $record->id,
                    ])),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                        ->visible(fn () => Auth::check() ? Auth::user()->role == 2 : false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SubcategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMainCategories::route('/'),
            'create' => Pages\CreateMainCategorie::route('/create'),
            'edit' => Pages\EditMainCategorie::route('/{record}/edit'),
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
        return auth()->user()->hasRole('admin') || auth()->user()->id === $record->user_id;
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
        return __('قسم رئيسي');
    }

    public static function getPluralLabel(): string
    {
        return 'الأقسام الرئيسية';
    }
}
