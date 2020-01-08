<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    public function prices()
    {
        return $this->hasMany('App\Model\ProductPrice');
    }

    public function price($shop_type)
    {
        if( $price = $this->hasMany('App\Model\ProductPrice')->where('shop_type_id', $shop_type)->first() )
        {
            return $price->price;
        }   

        return 0;
    }
}
