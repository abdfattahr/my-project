<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Supermarket extends Model
{


    protected $fillable = ['name', 'email', 'position', 'phone_number', 'image','user_id'];
    protected $hidden = ['password', 'remember_token'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }
    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class, 'advertisement_supermarkets', 'supermarket_id', 'advertisement_id')
                    ->withPivot('date_publication')
                    ->withTimestamps();
    }

    public function deliveryWorkers()
    {
        return $this->belongsToMany(DeliveryWorker::class, 'supermarket_delivery_workers')
                    ->withPivot('name', 'phone', 'delivery_time')
                    ->withTimestamps();

    }
    // علاقة كثيرة إلى كثيرين مع المنتجات
    public function products()
    {
        return $this->belongsToMany(Product::class, 'supermarkt_products')
            ->withPivot('stock')
            ->withTimestamps();
    }
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

     public function user()
     {
         return $this->belongsTo(User::class);
     }
     protected static function booted()
     {
         static::created(function ($supermarket) {
             // إنشاء رقم هاتف افتراضي للمتجر
             $supermarket->phones()->create([
                 'phone_number' => '0000000000', // يمكنك تعديل هذا الرقم حسب الحاجة
             ]);
         });
     }
}
