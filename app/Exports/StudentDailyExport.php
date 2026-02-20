<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentDailyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $students;
    protected $date;

    public function __construct($students, $date)
    {
        $this->students = $students;
        $this->date = $date;
    }

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Waktu Masuk',
            'Waktu Pulang',
            'Status',
            'Keterangan'
        ];
    }

    public function map($student): array
    {
        // Logic similar to view
        $attendance = $student->attendances->first();
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
            $student->nis,
            $student->name,
            $student->classRoom->name ?? '-',
            $attendance ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-',
            ($attendance && $attendance->check_out) ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-',
            $statusText,
            $attendance->note ?? '-'
        ];
    }
}
