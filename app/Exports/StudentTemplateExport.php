<?php

namespace App\Exports;

use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template' => new StudentDataSheet(),
            'Reference' => new ReferenceSheet(),
        ];
    }
}