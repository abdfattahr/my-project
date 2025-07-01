<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    protected static ?string $title = 'الفواتير';
    protected static ?string $modelLabel = 'فاتورة';
    protected static ?string $pluralModelLabel = 'الفواتير';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'accepted' => 'مقبول',
                        'cancelled' => 'ملغى',
                    ])
                    ->required()
                    ->disabled(),
                   Forms\Components\TextInput::make('total_price')
                    ->label('إجمالي السعر')
                    ->numeric()
                    ->required()
                    ->disabled(), // تعطيل تعديل السعر يدويًا
                Forms\Components\Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'كاش',
                        'points' => 'نقاط',
                    ])
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('supermarket_id')
                    ->label('المتجر')
                    ->relationship('supermarket', 'name')
                    ->default(fn () => auth()->user()->supermarket?->id)
                    ->disabled(),
                Forms\Components\Select::make('customer_id')
                    ->label('الزبون')
                    ->relationship('customer', 'name')
                    ->required()
                    ->disabled(),
                    Forms\Components\Select::make('customer_id')
                    ->label('رقم الزبون')
                    ->relationship('customer', 'phone_number')
                    ->disabled(),

             // تعطيل التعديل اليدوي للحالة
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('إجمالي السعر')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ل.س'),
                Tables\Columns\TextColumn::make('information')->label('المعلومات'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'معلق',
                        'accepted' => 'مقبول',
                        'cancelled' => 'ملغى',
                        default => 'غير معروف',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'cash' => 'success',
                        'points' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'كاش',
                        'points' => 'نقاط',
                        default => 'غير معروف',
                    }),
                Tables\Columns\TextColumn::make('supermarket.name')->label('اسم المتجر'),
                Tables\Columns\TextColumn::make('customer.name')->label('اسم الزبون')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
                Tables\Columns\TextColumn::make('customer.phone_number')->label('رقم الزبون')->searchable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'accepted' => 'مقبول',
                        'cancelled' => 'ملغى',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'نقدًا',
                        'points' => 'نقاط',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض التفاصيل'),
                Tables\Actions\EditAction::make()->label('تعديل')
                    ->visible(fn () => auth()->user()->hasRole('admin')), // التعديل للمدير فقط
                Tables\Actions\DeleteAction::make()->label('حذف')
                    ->visible(fn () => auth()->user()->hasRole('admin')), // الحذف للمدير فقط
            ])
            ->bulkActions([]);
    }

    public  function canCreate(): bool
    {
        return false; // تعطيل إنشاء فواتير جديدة من داخل الطلب
    }

    public  function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin'); // التعديل للمدير فقط
    }

    public  function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin'); // الحذف للمدير فقط
    }
}
