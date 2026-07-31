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

        // Normalisasi Nomor Telepon untuk WA
        $phone = $this->formatPhoneNumber($row['no_telepon'] ?? null);

        return new Student([
            'name'        => trim($row['nama_lengkap']),
            'nis'         => trim($row['nis']),
            'class_id'    => $classId,
            'gender'      => strtoupper(trim($row['jenis_kelamin_lp'])),
            'phone'       => $phone,
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
            
            // UBAH VALIDASI: Gunakan regex agar menerima string angka & strip/spasi
            'no_telepon' => ['nullable', 'regex:/^[0-9\+\-\s]+$/', 'min:9', 'max:15'],
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
            'no_telepon.regex' => 'Format no telepon tidak valid (harus berupa angka).',
            'no_telepon.min' => 'No telepon minimal 9 digit.',
            'no_telepon.max' => 'No telepon maksimal 15 digit.',
        ];
    }

    /**
     * Helper untuk merapikan nomor HP agar siap digunakan pada API WA
     */
    private function formatPhoneNumber($phone)
    {
        if (!$phone) return null;

        // 1. Buang semua karakter selain angka
        $cleaned = preg_replace('/[^0-9]/', '', (string)$phone);

        // 2. Jika user menginput '8123456789' (tanpa 0 di depan), tambahkan '0'
        if (Str::startsWith($cleaned, '8')) {
            $cleaned = '0' . $cleaned;
        }

        // Catatan: Jika API WA Anda butuh format '628...', Anda bisa ubah logika di sini:
        // if (Str::startsWith($cleaned, '0')) {
        //     $cleaned = '62' . substr($cleaned, 1);
        // }

        return $cleaned;
    }
}