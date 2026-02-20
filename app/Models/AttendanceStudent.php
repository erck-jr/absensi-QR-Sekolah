<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceStudent extends Model
{
    protected $fillable = ['student_id', 'shift_id', 'attendance_id', 'dates', 'check_in', 'check_out', 'is_late', 'note'];

    protected $casts = [
        'dates' => 'date',
        'is_late' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
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
