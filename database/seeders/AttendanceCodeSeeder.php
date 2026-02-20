<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = ['Hadir', 'Sakit', 'Izin', 'Alpha'];
        foreach ($codes as $code) {
            \App\Models\AttendanceCode::create(['name' => $code]);
        }
    }
}
