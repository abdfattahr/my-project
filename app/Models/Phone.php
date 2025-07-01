<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $fillable = ['supermarket_id', 'phone_number'];


public function supermarket()
{
    return $this->belongsTo(Supermarket::class, 'supermarket_id');
}


}
