<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherMonthlyExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $teachers;
    protected $month;
    protected $year;

    public function __construct($teachers, $month, $year)
    {
        $this->teachers = $teachers;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        return view('reports.exports.teacher_monthly', [
            'teachers' => $this->teachers,
            'month' => $this->month,
            'year' => $this->year
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
