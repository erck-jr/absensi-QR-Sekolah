<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['dates', 'info', 'group_id'];

    protected $casts = [
        'dates' => 'date',
    ];
}
