<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class StudentTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'NIS',
            'Kelas (Pilih dari Dropdown)',
            'Jenis Kelamin (L/P)',
            'No Telepon',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get all classes for dropdown
                $classes = SchoolClass::with('level')->get()->map(function ($class) {
                    return $class->level->name . ' - ' . $class->name;
                })->toArray();

                $classOptions = '"' . implode(',', $classes) . '"';
                $genderOptions = '"L,P"';

                // Data Validation for Class (Column C)
                $validationClass = $sheet->getDataValidation('C2:C1000');
                $validationClass->setType(DataValidation::TYPE_LIST);
                $validationClass->setErrorStyle(DataValidation::STYLE_STOP);
                $validationClass->setAllowBlank(false);
                $validationClass->setShowInputMessage(true);
                $validationClass->setShowErrorMessage(true);
                $validationClass->setShowDropDown(true);
                $validationClass->setErrorTitle('Input Error');
                $validationClass->setError('Silakan pilih kelas yang tersedia dari daftar.');
                $validationClass->setFormula1($classOptions);

                // Data Validation for Gender (Column D)
                $validationGender = $sheet->getDataValidation('D2:D1000');
                $validationGender->setType(DataValidation::TYPE_LIST);
                $validationGender->setErrorStyle(DataValidation::STYLE_STOP);
                $validationGender->setAllowBlank(false);
                $validationGender->setShowInputMessage(true);
                $validationGender->setShowErrorMessage(true);
                $validationGender->setShowDropDown(true);
                $validationGender->setErrorTitle('Input Error');
                $validationGender->setError('Silakan pilih L untuk Laki-laki atau P untuk Perempuan.');
                $validationGender->setFormula1($genderOptions);

                // Styling headings
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(20);
            },
        ];
    }
}
