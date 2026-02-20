<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = ['name', 'origin', 'meet_with', 'necessity', 'phone', 'check_in', 'check_out'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
}
