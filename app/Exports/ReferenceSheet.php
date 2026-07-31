<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReferenceSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Reference';
    }

    public function headings(): array
    {
        return ['Daftar Kelas'];
    }

    public function collection()
    {
        return SchoolClass::with('level')->get()->map(function ($class) {
            // Normalisasi per-komponen (sama dengan StudentImport) agar nilai
            // dropdown di kolom C persis cocok dengan key di map lookup import.
            return [
                'class_name' => trim($class->level->name) . ' - ' . trim($class->name),
            ];
        });
    }
}