<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = ['nuptk', 'name', 'gender', 'phone', 'unique_code', 'photo'];

    public function attendances()
    {
        return $this->hasMany(AttendanceTeacher::class);
    }
}
