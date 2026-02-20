<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceTeacher extends Model
{
    protected $fillable = ['teacher_id', 'shift_id', 'attendance_id', 'dates', 'check_in', 'check_out', 'is_late', 'note'];

    protected $casts = [
        'dates' => 'date',
        'is_late' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceCode()
    {
        return $this->belongsTo(AttendanceCode::class, 'attendance_id');
    }
}
