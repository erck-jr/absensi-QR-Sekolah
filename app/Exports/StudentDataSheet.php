<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StudentDataSheet implements WithHeadings, WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Template Import';
    }

    public function headings(): array
    {
        // PENTING: Nama header ini harus menghasilkan slug yang PERSIS sama
        // dengan key yang dipakai di StudentImport.php saat WithHeadingRow aktif.
        // Aturan slug: huruf kecil, spasi/tanda baca non-alfanumerik -> underscore.
        // 'Nama Lengkap'           -> nama_lengkap
        // 'NIS'                    -> nis
        // 'Kelas'                  -> kelas
        // 'Jenis Kelamin (L atau P)' -> jenis_kelamin_l_atau_p  <- TIDAK COCOK
        // Gunakan header yang menghasilkan slug simpel & pasti:
        return [
            'Nama Lengkap',        // -> nama_lengkap
            'NIS',                 // -> nis
            'Kelas',               // -> kelas
            'Jenis Kelamin',       // -> jenis_kelamin
            'No Telepon',          // -> no_telepon
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Hitung total kelas untuk menentukan range formula
                $totalClasses = SchoolClass::count();
                $maxClassRow = $totalClasses > 0 ? $totalClasses + 1 : 2;

                // Referensi range ke Reference Sheet (A2 sampai A{n})
                $classFormula = "=Reference!\$A\$2:\$A\${$maxClassRow}";
                $genderOptions = '"L,P"';

                // Data Validation untuk Kelas (Kolom C)
                $validationClass = $sheet->getDataValidation('C2:C1000');
                $validationClass->setType(DataValidation::TYPE_LIST);
                $validationClass->setErrorStyle(DataValidation::STYLE_STOP);
                $validationClass->setAllowBlank(true);
                $validationClass->setShowInputMessage(true);
                $validationClass->setShowErrorMessage(true);
                $validationClass->setShowDropDown(false);
                $validationClass->setErrorTitle('Input Error');
                $validationClass->setError('Silakan pilih kelas yang tersedia dari daftar.');
                $validationClass->setFormula1($classFormula);

                // Data Validation untuk Gender (Kolom D)
                $validationGender = $sheet->getDataValidation('D2:D1000');
                $validationGender->setType(DataValidation::TYPE_LIST);
                $validationGender->setErrorStyle(DataValidation::STYLE_STOP);
                $validationGender->setAllowBlank(true);
                $validationGender->setShowInputMessage(true);
                $validationGender->setShowErrorMessage(true);
                $validationGender->setShowDropDown(false);
                $validationGender->setErrorTitle('Input Error');
                $validationGender->setError('Silakan pilih L untuk Laki-laki atau P untuk Perempuan.');
                $validationGender->setFormula1($genderOptions);

                $sheet->getStyle('B2:B1500')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                $sheet->getStyle('E2:E1500')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
                
                // Styling
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