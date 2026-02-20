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
        // Cache classes for faster lookup
        $this->classes = SchoolClass::with('level')->get()->mapWithKeys(function ($class) {
            return [$class->level->name . ' - ' . $class->name => $class->id];
        })->toArray();
    }

    public function model(array $row)
    {
        $classId = $this->classes[$row['kelas_pilih_dari_dropdown']] ?? null;

        return new Student([
            'name'        => $row['nama_lengkap'],
            'nis'         => $row['nis'],
            'class_id'    => $classId,
            'gender'      => strtoupper($row['jenis_kelamin_lp']),
            'phone'       => $row['no_telepon'],
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
                    if (!isset($this->classes[$value])) {
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
            'nama_lengkap.required' => 'Nama lengkap wajib diisi pada baris :attribute.',
            'nis.required' => 'NIS wajib diisi pada baris :attribute.',
            'nis.unique' => 'NIS :input sudah terdaftar di sistem (baris :attribute).',
            'kelas_pilih_dari_dropdown.required' => 'Kelas wajib dipilih pada baris :attribute.',
            'jenis_kelamin_lp.in' => 'Jenis kelamin harus L atau P (baris :attribute).',
        ];
    }
}
