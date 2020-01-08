<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShopType extends Model
{
    public $timestamps = false;

    protected $fillable = ['type', 'delivery_price', 'delivery_status'];


    public function status()
    {
        return $this->belongsTo('App\Model\OrderStatus', 'delivery_status'); 
    }
}
