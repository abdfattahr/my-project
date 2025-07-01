<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification; // تأكد إن السطر ده موجود وصحيح
class Order extends Model
{
    protected $fillable = [
        'id',
        'invoice_id',
        'product_id',
        'unit_price',
        'amount',
        'date_order',
        'status','location'
    ];

    // تحويل الحقول إلى أنواع بيانات محددة
    protected $casts = [
        'date_order' => 'datetime', // تحويل حقل التاريخ إلى كائن DateTime
        'amount' => 'integer',
        'unit_price' => 'float'
    ];

    // علاقة مع فاتورة الطلب
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // علاقة مع المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($order) {
            $order->load('invoice.supermarket.user', 'invoice.customer');

            $vendor = $order->invoice->supermarket->user ?? null;
            if ($vendor) {
                Notification::make()
                    ->title('طلب جديد')
                    ->body('تم إنشاء طلب جديد #' . $order->id . ' من الزبون ' . ($order->invoice->customer->name ?? 'غير معروف'))
                    ->success()
                    ->sendToDatabase($vendor);
            }

            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('طلب جديد')
                    ->body('تم إنشاء طلب جديد #' . $order->id . ' من الزبون ' . ($order->invoice->customer->name ?? 'غير معروف'))
                    ->success()
                    ->sendToDatabase($admin);
            }
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $order->load('invoice.customer');
                $customer = $order->invoice->customer ?? null;

                if ($customer) {
                    $message = $order->status === 'accepted'
                        ? 'تم قبول طلبك #' . $order->id
                        : 'تم رفض طلبك #' . $order->id . ($order->rejection_reason ? ' بسبب: ' . $order->rejection_reason : '');

                    Notification::make()
                        ->title('تحديث حالة الطلب')
                        ->body($message)
                        ->success()
                        ->sendToDatabase($customer); // أرسل الإشعار مباشرة للـ Customer
                }
            }
        });
    }
}
