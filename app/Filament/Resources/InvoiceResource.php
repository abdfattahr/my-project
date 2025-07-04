<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationLabel = 'الفواتير';

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';

    protected static ?string $navigationGroup = 'إدارة المتجر';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total_price')
                    ->label('إجمالي السعر')
                    ->numeric()
                    ->required()
                    ->disabled(), // تعطيل تعديل السعر يدويًا
                Forms\Components\Textarea::make('information')
                    ->label('المعلومات')
                    ->maxLength(150)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'accepted' => 'مقبول',
                        'cancelled' => 'ملغى',
                    ])
                    ->disabled() // تعطيل تعديل الحالة يدويًا
                    ->required(),
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
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->when(
                        auth()->user()->hasRole('vendor') && !auth()->user()->hasRole('admin'),
                        fn ($query) => $query->where('supermarket_id', auth()->user()->supermarket?->id)
                    )
                    ->with(['supermarket', 'customer'])
            )
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
                Tables\Columns\TextColumn::make('customer.phone_number')->label('رقم الزبون')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('الزبون')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
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
            ->actions([

                Tables\Actions\ViewAction::make()->label('عرض التفاصيل'),
                Tables\Actions\EditAction::make()->label('تعديل')->visible(fn () => auth()->user()->hasRole('admin')), // التعديل للمدير فقط
                Tables\Actions\DeleteAction::make()->label('حذف')->visible(fn () => auth()->user()->hasRole('admin')),
            ],position:ActionsPosition::BeforeColumns)
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // تعطيل إنشاء الفاتورة يدويًا
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin'); // التعديل للمدير فقط
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
        return __('فاتورة');
    }

    public static function getPluralLabel(): string
    {
        return __('الفواتير📠');
    }
}
