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

    public function getWorkDurationAttribute()
    {
        if (!$this->check_in || !$this->check_out) {
            return '-';
        }

        $start = \Carbon\Carbon::parse($this->check_in);
        $end = \Carbon\Carbon::parse($this->check_out);

        $diff = $start->diff($end);
        
        return $diff->format('%Hj %Im');
    }
}
