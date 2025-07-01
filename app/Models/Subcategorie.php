<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategorie extends Model
{

    protected $fillable = ['name', 'main_category_id','icon'];

    public function mainCategory()
    {
        return $this->belongsTo(MainCategorie::class,'main_category_id');
    }

    public function products()
    {
        return $this->hasMany(product::class,'subcategory_id');
    }
}
