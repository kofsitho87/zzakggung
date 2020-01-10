<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id', 
        'product_id', 
        'option',
        'qty', 
        'receiver', 
        'phone_1', 
        'phone_2', 
        'zipcode', 
        'address', 
        'delivery_message', 
        'delivery_price', 

        'product_name', 
        'price',
        'model_id',
        'can_upload',
        'set_item',
        'set_master',
        'set_count',
    ];
    
    protected $appends = ['product_price'];
    // protected $attributes = [
    //     'productPrice'
    // ];

    // public function getProductAttribute()
    // {
    //     return $this->product;
    // }

    public function getProductPriceAttribute()
    {
        return $this->product->price($this->user->shop_type_id);
    }

    public function product()
    {
        return $this->belongsTo('\App\Model\Product', 'product_id');
    }


    public function status()
    {
        return $this->belongsTo('\App\Model\OrderStatus', 'delivery_status');
    }

    public function user()
    {
        return $this->belongsTo('\App\Model\User', 'user_id');
    }

    public function deliveryProvider()
    {
        return $this->belongsTo('\App\Model\DeliveryProvider', 'delivery_provider');
    }

    public function message()
    {
        return $this->hasOne('\App\Model\OrderMessage');
    }
}
