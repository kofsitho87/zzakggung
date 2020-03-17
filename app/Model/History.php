<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'update_history';

    protected $fillable = [
        "title",
        "desc",
        "status"
    ];
}
