<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TeacherDailyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $teachers;
    protected $date;

    public function __construct($teachers, $date)
    {
        $this->teachers = $teachers;
        $this->date = $date;
    }

    public function collection()
    {
        return $this->teachers;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NUPTK',
            'Nama Guru',
            'Waktu Masuk',
            'Waktu Pulang',
            'Total Jam Kerja',
            'Status',
            'Keterangan'
        ];
    }

    public function map($teacher): array
    {
        $attendance = $teacher->attendances->first();
        $statusText = 'Belum Absensi';
        
        if ($attendance) {
            $statusText = $attendance->attendanceCode->name;
            if ($attendance->is_late && $statusText === 'Hadir') {
                $statusText .= ' (Terlambat)';
            }
        } else {
            $today = \Carbon\Carbon::today()->toDateString();
            if ($this->date > $today) {
                $statusText = 'Belum Tersedia';
            } elseif ($this->date < $today) {
                $statusText = 'Tidak Hadir (Alpa)';
            }
        }

        return [
            \Carbon\Carbon::parse($this->date)->format('d/m/Y'),
            $teacher->nuptk,
            $teacher->name,
            $attendance ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-',
            ($attendance && $attendance->check_out) ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-',
            $attendance ? $attendance->work_duration : '-',
            $statusText,
            $attendance->note ?? '-'
        ];
    }
}
