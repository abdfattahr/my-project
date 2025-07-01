<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainCategorie extends Model
{

    protected $fillable = ['name','icon'];
    public $timestamps = true;

    public function subcategories()
    {
        return $this->hasMany(Subcategorie::class, 'main_category_id');
    }
}
