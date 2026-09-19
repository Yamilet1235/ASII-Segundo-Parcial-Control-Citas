<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('appointments')->insert([
            [
                'patient_id' => 1,
                'doctor_id' => 1,
                'start_at' => '2026-09-20 09:00:00',
                'end_at' => '2026-09-20 09:30:00',
                'reason' => 'Consulta general',
                'status' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}