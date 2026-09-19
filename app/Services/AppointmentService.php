<?php

namespace App\Services;

use App\Exceptions\ScheduleConflictException;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            $doctorId = (int) $data['doctor_id'];

            $this->lockDoctor($doctorId);

            if (($data['status'] ?? 'pendiente') !== 'cancelada') {
                $this->ensureScheduleIsAvailable(
                    $doctorId,
                    $data['start_at'],
                    $data['end_at']
                );
            }

            return Appointment::create($data)
                ->load(['patient', 'doctor']);
        });
    }

    public function update(int $id, array $data): Appointment
    {
        return DB::transaction(function () use ($id, $data) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $doctorId = (int) ($data['doctor_id'] ?? $appointment->doctor_id);
            $startAt = $data['start_at'] ?? $appointment->start_at;
            $endAt = $data['end_at'] ?? $appointment->end_at;
            $status = $data['status'] ?? $appointment->status;

            $this->lockDoctor($doctorId);

            if ($status !== 'cancelada') {
                $this->ensureScheduleIsAvailable(
                    $doctorId,
                    $startAt,
                    $endAt,
                    $appointment->id
                );
            }

            $appointment->update($data);

            return $appointment->load(['patient', 'doctor']);
        });
    }

    public function changeStatus(int $id, string $status): Appointment
    {
        return DB::transaction(function () use ($id, $status) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $this->lockDoctor($appointment->doctor_id);

            if ($status !== 'cancelada') {
                $this->ensureScheduleIsAvailable(
                    $appointment->doctor_id,
                    $appointment->start_at,
                    $appointment->end_at,
                    $appointment->id
                );
            }

            $appointment->update([
                'status' => $status,
            ]);

            return $appointment->load(['patient', 'doctor']);
        });
    }

    private function lockDoctor(int $doctorId): void
    {
        Doctor::query()
            ->whereKey($doctorId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureScheduleIsAvailable(
        int $doctorId,
        string $startAt,
        string $endAt,
        ?int $ignoredAppointmentId = null
    ): void {
        $hasConflict = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelada')
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->when($ignoredAppointmentId, function ($query, $id) {
                $query->whereKeyNot($id);
            })
            ->exists();

        if ($hasConflict) {
            throw new ScheduleConflictException;
        }
    }
}
