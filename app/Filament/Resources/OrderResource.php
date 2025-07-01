<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\User; // إضافة للوصول إلى المستخدمين
use App\Notifications\OrderAcceptedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationGroup = 'إدارة المتجر';



 public static function getNavigationBadge(): ?string
{
    $count = static::getModel()::query()
        ->where('status', '=', 'pending')
        ->when(auth()->user()->hasRole('vendor'), function (Builder $query) {
            $supermarket = auth()->user()->supermarket;
            if ($supermarket) {
                $query->whereHas('invoice', function (Builder $q) use ($supermarket) {
                    $q->where('supermarket_id', $supermarket->id);
                });
            }
        })
        ->count();

    return $count >= 0 ? (string) $count : null; // يرجع العدد كسلسلة إذا موجود، أو null إذا 0
}
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static ::getmodel()::where('status','=','pending')->count() >0 ?'warning':'primary';
    }


    public static function getAddressFromCoordinates($coordinates)
    {
        try {
            $client = new Client();
            [$lat, $lon] = explode(',', $coordinates);

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                    'addressdetails' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'YourAppName/1.0',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['display_name'] ?? 'غير متوفر';
        } catch (\Exception $e) {
            return 'خطأ في استرجاع الموقع: ' . $e->getMessage();
        }
    }

    public static function form(Form $form): Form
    {
        // نموذج لإنشاء طلب جديد (للعملاء)
        if ($form->getOperation() === 'create') {
            return $form->schema([
                Forms\Components\TextInput::make('unit_price')
                ->label('سعر الوحدة')
                ->numeric()
                ->required(),
                Forms\Components\DatePicker::make('date_order')->
                label('تاريخ الطلب')
                ->required(),
                Forms\Components\Select::make('product_id')
                ->label('المنتج')
                ->relationship('product', 'name')
                ->required(),
                Forms\Components\TextInput::make('amount')
                ->label('الكمية')
                ->numeric()->required(),
                Forms\Components\TextInput::make('location')
                ->label('الموقع')
                ->required(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(['pending' => 'معلق'])
                    ->default('pending')
                    ->disabled() // لا يمكن تغيير الحالة عند الإنشاء
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(['cash' => 'كاش', 'points' => 'نقاط'])
                    ->required(),
                Forms\Components\Select::make('supermarket_id')
                    ->label('المتجر')
                    ->relationship('supermarket', 'name')
                    ->required(),
            ]);
        }

        // نموذج لتعديل حالة الطلب فقط (للتاجر أو المدير)
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'accepted' => 'مقبول',
                    'rejected' => 'مرفوض',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->when(
                        auth()->user()->hasRole('vendor') && !auth()->user()->hasRole('admin'),
                        fn ($query) => $query->whereHas('invoice', fn ($q) => $q->where('supermarket_id', auth()->user()->supermarket?->id))
                    )
                    ->with(['invoice.supermarket', 'invoice.customer', 'product'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('رقم الطلب'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'معلق',
                        'accepted' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => 'غير معروف',
                    }),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->default('غير متوفر'),
                    Tables\Columns\TextColumn::make('amount')
                    ->label('الكمية'),
                Tables\Columns\TextColumn::make('unit_price')
                ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ل.س'),
                Tables\Columns\TextColumn::make('date_order')
                    ->label('تاريخ الطلب')
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.id')->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('invoice.supermarket.name')
                    ->label('اسم المتجر')
                    ->searchable()
                    ->default('غير متوفر'),
                Tables\Columns\TextColumn::make('location')
                    ->label('الموقع')
                    ->formatStateUsing(fn ($state) => static::getAddressFromCoordinates($state)),

                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('invoice_id')
                    ->label('الفواتير')
                    ->relationship('invoice', 'id')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('accept')
    ->label('قبول')
    ->icon('heroicon-o-check')
    ->color('success')
    ->requiresConfirmation()
    ->action(function (Order $record) {
        try {
            // تحديث حالة الطلب
            $record->update(['status' => 'accepted', 'rejection_reason' => null]);

            // تحديث حالة الفاتورة
            $invoice = $record->invoice;
            if ($invoice) {
                $invoice->update(['status' => 'accepted']);

                // إضافة النقاط للعميل
                $customer = $invoice->customer;
                if ($customer) {
                    $pointsToAdd = $record->amount * 6;
                    $customer->increment('points', $pointsToAdd);

                    // إرسال إشعار للعميل
                    try {
                        $customer->notifyNow(new OrderAcceptedNotification($record)); // تغيير إلى notifyNow
                        Log::info('تم إرسال الإشعار للعميل', [
                            'customer_id' => $customer->id,
                            'order_id' => $record->id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('فشل إرسال الإشعار', [
                            'customer_id' => $customer->id,
                            'order_id' => $record->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    Log::info('تم إضافة نقاط للعميل', [
                        'customer_id' => $customer->id,
                        'order_id' => $record->id,
                        'points_added' => $pointsToAdd,
                    ]);
                } else {
                    Log::warning('لم يتم العثور على عميل للفاتورة', [
                        'invoice_id' => $invoice->id,
                        'order_id' => $record->id,
                    ]);
                }
            } else {
                Log::warning('لم يتم العثور على فاتورة للطلب', [
                    'order_id' => $record->id,
                ]);
            }

            // إرسال إشعار للتاجر/المدير
            Notification::make()
                ->title('تم قبول الطلب #' . $record->id)
                ->success()
                ->send();

            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('فشل قبول الطلب', [
                'order_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('حدث خطأ أثناء قبول الطلب')
                ->danger()
                ->send();

            return redirect()->back();
        }
    })
    ->visible(fn (Order $record) => $record->status === 'pending' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('vendor'))),
    Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        try {
                            // تحديث حالة الطلب
                            $record->update(['status' => 'rejected']);

                            $invoice = $record->invoice;
                             if ($invoice) {
                                 $invoice->update(['status' => 'cancelled']);
                            }

                            // // حذف الفاتورة
                            // $invoice = $record->invoice;
                            // if ($invoice) {
                            //     $invoice->delete();
                            // }

                            // // حذف الطلب
                            // $record->delete();

                            // إرسال إشعار للتاجر أو المدير
                            Notification::make()
                                ->title('تم رفض الطلب #' . $record->id)
                                ->success()
                                ->send();

                            return redirect()->back();
                        } catch (\Exception $e) {
                            Log::error('فشل رفض الطلب', [
                                'order_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('حدث خطأ أثناء رفض الطلب')
                                ->danger()
                                ->send();

                            return redirect()->back();
                        }
                    })
                    ->visible(fn (Order $record) => $record->status === 'pending' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('vendor'))),
                Tables\Actions\ViewAction::make()->label('عرض طلب'),
      //          Tables\Actions\EditAction::make()->label('تعديل حالة'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\InvoicesRelationManager::class,];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'create' => Pages\CreateOrder::route('/create'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('customer');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('vendor');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['admin', 'vendor', 'customer']);
    }

    public static function getLabel(): string
    {
        return __('طلب');
    }

    public static function getPluralLabel(): string
    {
        return __('الطلبات 🛍');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function (Order $order) {
            $invoice = Invoice::create([
                'total_price' => $order->unit_price * $order->amount,
                'information' => 'فاتورة تم إنشاؤها تلقائيًا لطلب #' . $order->id,
                'status' => 'pending',
                'payment_method' => $order->payment_method,
                'supermarket_id' => $order->supermarket_id,
                'customer_id' => auth()->user()->id,
            ]);

            $order->update(['invoice_id' => $invoice->id]);

            // إرسال إشعار إلى التاجر المرتبط بالسوبرماركت أو المدير
            $vendor = User::whereHas('roles', fn ($query) => $query->where('name', 'vendor'))
                ->whereHas('supermarket', fn ($query) => $query->where('id', $order->supermarket_id))
                ->first();

            if ($vendor) {
                Notification::make()
                    ->title('طلب جديد معلق')
                    ->body('تم استلام طلب جديد #' . $order->id . ' من العميل في متجرك.')
                    ->success()
                    ->sendTo($vendor); // إرسال الإشعار إلى التاجر
            }

            // إرسال إشعار إلى جميع المديرين
            $admins = User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('طلب جديد معلق')
                    ->body('تم استلام طلب جديد #' . $order->id . ' في المتجر #' . $order->supermarket_id . '.')
                    ->success()
                    ->sendTo($admin);
            }

            // إطلاق الحدث
            event(new \App\Events\OrderCreated($order));
        });
    }
}
