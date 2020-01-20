<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $casts = [
        "is_active" => "boolean",
    ];

    protected $fillable = [
        'content',
        'is_active'
    ];
}
