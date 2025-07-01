<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'image',
        'trade_mark_id',
        'subcategory_id',
        'description',

    ];
    public function orders()
    {
        return $this->belongsToMany  (Invoice::class, 'order')->withPivot([ 'id','unit_price', 'amount', 'date_order','location','status']);
    }
    public function subcategory ()
    {
        return $this->belongsTo(Subcategorie::class,'subcategory_id');
    }

    public function tradeMark()
    {
        return $this->belongsTo(TradeMark::class,'trade_mark_id');
    }
    public function productreviews(){

        return $this->belongsToMany(Product::class, 'product_review')  ->withPivot(['customer_id', 'product_id','rating'])
        ->withTimestamps();
}

public function favoriteByCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_favorites')  ->withPivot(['is_active', 'notes'])
        ->withTimestamps();;
    }
   // علاقة كثيرة إلى كثيرين مع السوبر ماركت
public function supermarkets()
    {
    return $this->belongsToMany(Supermarket::class,'supermarkt_products')
            ->withPivot('stock')->withTimestamps();
                }

}
