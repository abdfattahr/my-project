<?php

namespace App\Filament\Resources\MainCategorieResource\RelationManagers;

use App\Models\Subcategorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subcategories'; // اسم العلاقة في النموذج (يجب أن تكون موجودة في MainCategorie Model)

    protected static ?string $title = 'الأقسام الفرعية';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم القسم الفرعي')
                    ->required()
                    ->maxLength(20),

                Forms\Components\Select::make('icon')
                    ->label('الأيقونة')
                    ->options([
                        'heroicon-o-apple' => 'تفاحة',
                        'heroicon-o-cup' => 'كوب',
                        'heroicon-o-face-smile' => 'وجه مبتسم',
                        'heroicon-o-sparkles' => 'تنظيف',
                        'heroicon-o-headphones' => 'سماعات',
                    ])
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('الأيقونة')
                    ->getStateUsing(function ($record): ?string {
                        $availableIcons = [
                            'vegetable' => 'icons/icons8-vegetable-50.png',
                            'fruite' => 'icons/icons8-group-of-fruits-50.png',
                            'drink' => 'icons/icons8-drink-49.png',
                            'legumes' => 'icons/icons8-vegetable-51.png',
                            'electronic' => 'icons/icons8-electronic-64.png',
                            'spices' => 'icons/icons8-cinnamon-sticks-50.png',
                            'snacs' => 'icons/icons8-snack-50.png',
                            'makeup' => 'icons/icons8-makeup-50.png',
                        ];
                        $iconPath = $availableIcons[$record->icon] ?? 'icons/folder.png';
                        Log::info("Icon path for {$record->icon}: $iconPath");
                        return $iconPath;
                    })
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القسم الفرعي')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة قسم فرعي'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف'),
                ]),
            ]);
    }
}