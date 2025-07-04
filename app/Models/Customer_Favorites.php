<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer_Favorites extends Model
{
    protected $table = 'Customer_Favorites';

    protected $fillable = ['product_id', 'customer_id', 'notes', 'is_active']; 

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function favoriteByCustomers()
    {
        return $this->belongsToMany(Customer::class, 'customer_favorites')
                    ->using(Customer_Favorites::class)
                    ->withPivot(['is_active', 'notes'])
                    ->withTimestamps();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
