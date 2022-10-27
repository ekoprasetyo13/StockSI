<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['category_id','nama','harga','image','qty','link','product_code', 'description'];

    protected $hidden = ['created_at','updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
