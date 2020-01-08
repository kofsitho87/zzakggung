<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    //protected $guarded = ['user_id'];

    protected $fillable = ['user_id', 'is_plus',];

    // protected $attributes = [
    //     'plus', 'minus'
    // ];

    // public function getPlusAttribute()
    // {
    //     return $this->is_plus ? $this->price : 0;
    // }

    // public function getMinusAttribute()
    // {
    //     return $this->is_plus ? 0 : $this->price;
    // }

    public function user()
    {
        return $this->belongsTo('App\Model\User');
    }


}
