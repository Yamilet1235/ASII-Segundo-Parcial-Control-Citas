<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentService
{
    public function getAll(array $filters = [])
    {
        $query = Appointment::with(['patient', 'doctor']);

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['from'])) {
            $query->whereDate('start_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('start_at', '<=', $filters['to']);
        }

        return $query->orderBy('start_at')->get();
    }

    public function find(int $id): Appointment
    {
        return Appointment::with(['patient', 'doctor'])
            ->findOrFail($id);
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data)
            ->load(['patient', 'doctor']);
    }

    public function update(int $id, array $data): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($data);

        return $appointment->load(['patient', 'doctor']);
    }

    public function changeStatus(int $id, string $status): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => $status,
        ]);

        return $appointment->load(['patient', 'doctor']);
    }
}