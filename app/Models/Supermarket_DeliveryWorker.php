<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supermarket_DeliveryWorker extends Model
{
    protected $table = 'supermarket_delivery_workers';

    protected $fillable = ['supermarket_id', 'delivery_worker_id','name','phone','delivery_time'];

public function supermarket()
{
    return $this->belongsTo(Supermarket::class, 'supermarket_id');
}

public function deliveryWorker()
{
    return $this->belongsTo(DeliveryWorker::class, 'delivery_worker_id');
}
}
