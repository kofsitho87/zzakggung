<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

//use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    //use SoftDeletes;
    use Notifiable;

    public function orders()
    {
        return $this->hasMany('\App\Model\Order', 'user_id')->with('product');
    }

    public function shop_type()
    {
        return $this->belongsTo('\App\Model\ShopType', 'shop_type_id');
    }

    public function totalPrice()
    {
        return $this->orders->sum(function($order){
            $price = $order->product->price($this->shop_type_id); 
            return ($price * $order->qty) + $order->delivery_price - $order->minus_price;
        });
    }

    public function trades()
    {
        return $this->hasMany('\App\Model\Trade', 'user_id');
    }

    public function totalPlusPrice()
    {
        return $this->trades->sum(function($trade){
            return $trade->is_plus ? $trade->price : 0;
        });
    }

    public function tradeAvailblePrice()
    {
        return $this->trades->sum(function($trade){
            return $trade->is_plus ? $trade->price : -$trade->price;
        });
    }

    public function tradeTotalMinusPrice()
    {
        return $this->trades->sum(function($trade){
            return $trade->is_plus ? 0 : $trade->price;
        });
    }
}
