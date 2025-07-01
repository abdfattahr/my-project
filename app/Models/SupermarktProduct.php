<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupermarktProduct extends Model
{
    protected $table = 'supermarkt_products'; // استخدم أحرف صغيرة لتتطابق مع الهجرة
    protected $fillable = ['supermarket_id','product_id','stock',];

    public function supermarket()
    {
        return $this->belongsTo(Supermarket::class, 'supermarket_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
