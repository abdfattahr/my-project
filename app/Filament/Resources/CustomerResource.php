<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'الزبائن';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'إدارة عامة';

    // تعطيل إمكانية إنشاء زبائن يدويًا
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الزبون')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('postion')
                    ->label('الموقع')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('points')
                    ->label('النقاط')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الزبون'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الزبون')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('رقم الهاتف'),
                Tables\Columns\TextColumn::make('postion')
                    ->label('الموقع'),
                Tables\Columns\TextColumn::make('points')
                    ->label('النقاط'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\Filter::make('points')
                    ->form([
                        Forms\Components\TextInput::make('min_points')
                            ->label('الحد الأدنى للنقاط')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_points')
                            ->label('الحد الأقصى للنقاط')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['min_points']) {
                            $query->where('points', '>=', $data['min_points']);
                        }
                        if ($data['max_points']) {
                            $query->where('points', '<=', $data['max_points']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                // إزالة زر "إنشاء" من الجدول
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\InvoicesRelationManager::class,
            // RelationManagers\FavoritesRelationManager::class,
            // RelationManagers\ProductreviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): string
    {
        return __('الزبائن👨‍💼');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

}
