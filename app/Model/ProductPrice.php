<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'product_prices';
    //protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['shop_type_id', 'price'];

    public function shop_type()
    {
        return $this->belongsTo('App\Model\ShopType', 'shop_type_id');
    }
}
