<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderMessage extends Model
{
    protected $fillable = ['order_id', 'content'];
}
