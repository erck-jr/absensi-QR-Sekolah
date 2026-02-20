<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentMonthlyExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $students;
    protected $month;
    protected $year;

    public function __construct($students, $month, $year)
    {
        $this->students = $students;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        return view('reports.exports.student_monthly', [
            'students' => $this->students,
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
