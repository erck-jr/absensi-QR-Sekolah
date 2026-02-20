<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    protected $fillable = ['attendance_id', 'attendance_type', 'gateway_id', 'recipient_number', 'message_content', 'status', 'error_details'];

    public function gateway()
    {
        return $this->belongsTo(WaGateway::class);
    }
}
