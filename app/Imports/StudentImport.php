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
            // Normalisasi konsisten: trim setiap komponen SEBELUM digabung,
            // sama seperti yang dilakukan ReferenceSheet saat mengisi dropdown.
            $key = trim($class->level->name) . ' - ' . trim($class->name);
            return [$key => $class->id];
        })->toArray();
    }

    /**
     * Dipanggil SEBELUM validasi untuk setiap baris.
     *
     * Ini adalah pengganti SkipsEmptyRows yang lebih dapat diandalkan.
     * SkipsEmptyRows menggunakan array_filter() yang menganggap nilai '0', false,
     * atau string kosong sebagai "empty" — menyebabkan baris data valid ikut terfilter.
     *
     * Dengan prepareForValidation(), kita mengontrol secara eksplisit:
     * - Baris kosong (ghost row dari formatting Excel) → kembalikan [] kosong
     * - Aturan 'sometimes' pada rules() tidak akan aktif untuk array kosong
     * - Validasi tidak error, model() menangkap sisanya sebagai fallback
     */
    public function prepareForValidation(array $data, int $index): array
    {
        $namaLengkap = trim((string)($data['nama_lengkap'] ?? ''));
        $nis         = trim((string)($data['nis'] ?? ''));

        // Jika kedua field utama kosong, ini adalah baris kosong/ghost row
        if (empty($namaLengkap) && empty($nis)) {
            return []; // Array kosong → semua aturan 'sometimes' tidak aktif
        }

        return $data;
    }

    public function model(array $row)
    {
        // Fallback guard: jika baris kosong lolos ke sini, abaikan.
        if (empty(trim((string)($row['nama_lengkap'] ?? ''))) && empty(trim((string)($row['nis'] ?? '')))) {
            return null;
        }

        // Key 'kelas' sesuai slug dari header 'Kelas' di template.
        $selectedClass = isset($row['kelas'])
            ? trim($row['kelas'])
            : null;

        $classId = $this->classes[$selectedClass] ?? null;

        // Normalisasi Nomor Telepon untuk WA
        $phone = $this->formatPhoneNumber($row['no_telepon'] ?? null);

        // Key 'jenis_kelamin' sesuai slug dari header 'Jenis Kelamin' di template.
        return new Student([
            'name'        => trim($row['nama_lengkap']),
            'nis'         => trim((string)($row['nis'] ?? '')),
            'class_id'    => $classId,
            'gender'      => strtoupper(trim($row['jenis_kelamin'] ?? '')),
            'phone'       => $phone,
            'unique_code' => (string) Str::uuid(),
        ]);
    }

    public function rules(): array
    {
        return [
            // 'sometimes' → aturan hanya aktif jika field ADA di data (tidak aktif untuk baris kosong).
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'nis'          => 'sometimes|required|numeric|unique:students,nis',

            // Key 'kelas' sesuai slug dari header 'Kelas' di template
            'kelas' => [
                'sometimes',
                'required',
                function ($attribute, $value, $fail) {
                    $cleanedValue = trim((string)($value ?? ''));
                    if (!isset($this->classes[$cleanedValue])) {
                        $fail('Kelas "' . $value . '" tidak ditemukan di database.');
                    }
                },
            ],

            // Key 'jenis_kelamin' sesuai slug dari header 'Jenis Kelamin' di template
            'jenis_kelamin' => 'sometimes|required|in:L,P,l,p',

            // Validasi no_telepon: nullable, regex format, panjang min/max karakter
            'no_telepon' => ['nullable', 'regex:/^[0-9\+\-\s]+$/', 'min:9', 'max:15'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'nis.required'           => 'NIS wajib diisi.',
            'nis.unique'             => 'NIS :input sudah terdaftar di sistem.',
            'kelas.required'         => 'Kelas wajib dipilih dari dropdown.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi (L atau P).', // Sebelumnya tidak ada → muncul nama field mentah
            'jenis_kelamin.in'       => 'Jenis kelamin harus L atau P.',
            'no_telepon.regex'       => 'Format no telepon tidak valid (harus berupa angka).',
            'no_telepon.min'         => 'No telepon minimal 9 digit.',
            'no_telepon.max'         => 'No telepon maksimal 15 digit.',
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

        // Catatan: Jika API WA Anda butuh format '628...', ubah logika di sini:
        // if (Str::startsWith($cleaned, '0')) {
        //     $cleaned = '62' . substr($cleaned, 1);
        // }

        return $cleaned;
    }
}