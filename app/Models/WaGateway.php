<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaGateway extends Model
{
    protected $fillable = ['name', 'api_url', 'api_token', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
