<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['class_id', 'nis', 'name', 'gender', 'phone', 'unique_code', 'photo'];

    public function classRoom()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceStudent::class);
    }
}
