<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class StudentImport implements ToModel, WithHeadingRow, WithValidation
{
    private $classes;

    public function __construct()
    {
        // Cache classes dengan key yang disebarkan/ditrim
        $this->classes = SchoolClass::with('level')->get()->mapWithKeys(function ($class) {
            $key = trim($class->level->name . ' - ' . $class->name);
            return [$key => $class->id];
        })->toArray();
    }

    public function model(array $row)
    {
        $selectedClass = isset($row['kelas_pilih_dari_dropdown']) 
            ? trim($row['kelas_pilih_dari_dropdown']) 
            : null;

        $classId = $this->classes[$selectedClass] ?? null;

        return new Student([
            'name'        => trim($row['nama_lengkap']),
            'nis'         => trim($row['nis']),
            'class_id'    => $classId,
            'gender'      => strtoupper(trim($row['jenis_kelamin_lp'])),
            'phone'       => $row['no_telepon'] ?? null,
            'unique_code' => (string) Str::uuid(),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'nis' => 'required|numeric|unique:students,nis',
            'kelas_pilih_dari_dropdown' => [
                'required',
                function ($attribute, $value, $fail) {
                    $cleanedValue = trim($value);
                    if (!isset($this->classes[$cleanedValue])) {
                        $fail('Kelas "' . $value . '" tidak ditemukan di database.');
                    }
                },
            ],
            'jenis_kelamin_lp' => 'required|in:L,P,l,p',
            'no_telepon' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS :input sudah terdaftar di sistem.',
            'kelas_pilih_dari_dropdown.required' => 'Kelas wajib dipilih dari dropdown.',
            'jenis_kelamin_lp.in' => 'Jenis kelamin harus L atau P.',
        ];
    }
}