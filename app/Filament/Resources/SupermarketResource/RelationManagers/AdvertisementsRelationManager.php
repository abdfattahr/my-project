<?php

namespace App\Filament\Resources\SupermarketResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class AdvertisementsRelationManager extends RelationManager
{
    protected static string $relationship = 'advertisements';
    protected static ?string $title='اعلانات المتاجر';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date_publication')
                    ->label('تاريخ النشر')
                    ->required(),

                Forms\Components\TextInput::make('information')
                    ->label('معلومات إضافية')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('وصف الإعلان')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('صورة الإعلان')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('pivot.date_publication')
                    ->label('تاريخ النشر')
                    ->date(),

                Tables\Columns\TextColumn::make('pivot.information')
                    ->label('معلومات إضافية'),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn ($action) => [
                        $action->getRecordSelect(),
                        Forms\Components\DatePicker::make('date_publication')
                            ->label('تاريخ النشر')
                            ->required(),
                        Forms\Components\TextInput::make('information')
                            ->label('معلومات إضافية')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DetachAction::make()->label('إلغاء الربط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('إلغاء الربط'),
                ]),
            ]);
    }
}
