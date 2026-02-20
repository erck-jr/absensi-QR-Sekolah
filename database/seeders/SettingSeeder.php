<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'app_name' => 'Absensi QR Sekolah',
            'app_description' => 'Sistem Presensi Siswa & Guru Berbasis QR Code',
            'welcome_text' => 'Selamat Datang di Sistem Absensi Sekolah. Silakan scan kartu Anda pada scanner yang tersedia atau login untuk mengelola data.',
            'wa_api_url' => 'http://localhost:8000/send-message',
            'wa_api_key' => 'default_key',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
