<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementSupermarketResource\Pages;
use App\Models\Advertisement;
use App\Models\Supermarket;
use App\Models\Advertisement_Supermarket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as ComponentsSection;
use Illuminate\Support\Facades\Auth;

class AdvertisementSupermarketResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'إعلانات المتجر';
    protected static ?string $navigationGroup = 'إدارة الإعلانات';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ComponentsSection::make('مواصفات')
                    ->schema([
                        Forms\Components\DatePicker::make('date_publication')
                            ->label('تاريخ النشر')
                            ->required()
                            ->default('-'),

                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                // تصفية البيانات لعرض إعلانات المتجر الحالي للـ vendor، أو كل الإعلانات للـ admin
                return $user->hasRole('admin')
                    ? Advertisement_Supermarket::query()
                    : Advertisement_Supermarket::where('supermarket_id', $user->supermarket?->id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('date_publication')
                    ->label('تاريخ النشر')
                    ->date(),

                Tables\Columns\ImageColumn::make('advertisement.image')
                    ->label('صورة الإعلان')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('supermarket.name')
                    ->label('اسم المتجر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('advertisement.description')
                    ->label('وصف الإعلان')
                    ->getStateUsing(fn ($record) => $record->advertisement->description ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('advertisement_id')
                    ->label('الإعلان')
                    ->relationship('advertisement', 'description'),
                Tables\Filters\SelectFilter::make('supermarket_id')
                    ->label('المتجر')
                    ->relationship('supermarket', 'name')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->hasRole('admin');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('  تعديل النشر ')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->hasRole('admin');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $user->hasRole('admin');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف')
                        ->visible(function () {
                            /** @var \App\Models\User $user */
                            $user = Auth::user();
                            return $user->hasRole('admin');
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisementSupermarkets::route('/'),
            'create' => Pages\CreateAdvertisementSupermarket::route('/create'),
            'edit' => Pages\EditAdvertisementSupermarket::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false ;// فقط الأدمن يمكنه إنشاء إعلانات
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('admin'); // فقط الأدمن يمكنه التعديل
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('admin'); // فقط الأدمن يمكنه الحذف
    }


    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('admin') || ($user->hasRole('vendor') && $user->supermarket);
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('admin') || ($user->hasRole('vendor') && $user->supermarket);
    }

    public static function getLabel(): string
    {
        return __('إعلان 📣');
    }

    public static function getPluralLabel(): string
    {
        return __('🔗 إعلانات المتجر');
    }
}
