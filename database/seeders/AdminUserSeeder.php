<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        \App\Models\User::create([
            'name' => 'Operator',
            'email' => 'operator@operator.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);
    }
}
