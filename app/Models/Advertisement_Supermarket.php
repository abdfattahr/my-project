<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement_Supermarket extends Model
{
    protected $table = 'advertisement_supermarkets';

    protected $fillable = ['supermarket_id', 'advertisement_id', 'date_publication'];


public function supermarket()
{
    return $this->belongsTo(Supermarket::class, 'supermarket_id');
}

public function advertisement()
{
    return $this->belongsTo(Advertisement::class, 'advertisement_id');
}

}
