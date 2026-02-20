<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = ['key', 'content'];

    public function getLabelAttribute()
    {
        $labels = [
            'masuk_msg' => 'Pesan Masuk (Legacy)',
            'pulang_msg' => 'Pesan Pulang (Legacy)',
            'student_checkin' => 'Absen Masuk Siswa',
            'student_checkout' => 'Absen Pulang Siswa',
            'teacher_checkin' => 'Absen Masuk Guru',
            'teacher_checkout' => 'Absen Pulang Guru',
            'student_late_checkin' => 'Absen Masuk Terlambat (Siswa)',
            'student_early_checkout' => 'Absen Pulang Cepat (Siswa)',
        ];

        return $labels[$this->key] ?? ucwords(str_replace('_', ' ', $this->key));
    }
}
