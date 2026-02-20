<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Shift::create([
            'name' => 'Pagi Masuk',
            'check_in_time' => '07:00:00',
            'check_out_time' => '12:00:00',
            'late_check_in_minute' => 15,
        ]);
    }
}
