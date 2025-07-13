<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['id', 'total_price', 'information', 'customer_id', 'supermarket_id', 'payment_method', 'status'];

    public $timestamps = true;

    public function supermarket()
    {
        return $this->belongsTo(Supermarket::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'invoice_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order')->withPivot(['unit_price', 'amount', 'date_order', 'location', 'status']);
    }

    // حساب إجمالي السعر ديناميكيًا
    public function getTotalPriceAttribute()
    {
        return $this->orders->sum(function ($order) {
            return $order->unit_price * $order->amount;
        });
    }
}
