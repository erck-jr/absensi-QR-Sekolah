<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'check_in_time', 'check_out_time', 'late_check_in_minute', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'late_check_in_minute' => 'integer',
    ];
}
