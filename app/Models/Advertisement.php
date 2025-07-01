<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{

    protected $fillable = ['description','image'];
    public function supermarkets()
    {
        return $this->belongsToMany(Supermarket::class, 'advertisement_supermarkets', 'advertisement_id', 'supermarket_id')
                    ->withPivot('date_publication')
                    ->withTimestamps();
    }
}
