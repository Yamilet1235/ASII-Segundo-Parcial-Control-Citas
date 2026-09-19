<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('patients')->insert([
            [
                'name' => 'Ana López',
                'email' => 'ana@example.com',
                'phone' => '55550001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Carlos Pérez',
                'email' => 'carlos@example.com',
                'phone' => '55550002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}