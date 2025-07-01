<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryWorker extends Model
{
    protected $fillable = ['name','phone'];

    public $timestamps = true;
    public function supermarkets()
    {
        return $this->belongsToMany(Supermarket::class, 'supermarket_delivery_workers')
                    ->withPivot('name', 'phone', 'delivery_time')
                    ->withTimestamps();
    }
}
