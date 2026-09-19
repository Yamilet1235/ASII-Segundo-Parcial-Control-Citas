<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentConflictTest extends TestCase
{
    use RefreshDatabase;

    private Patient $patient;

    private Doctor $doctor;

    private Doctor $otherDoctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = Patient::create([
            'name' => 'Paciente de prueba',
            'email' => 'paciente@example.com',
        ]);
        $this->doctor = Doctor::create([
            'name' => 'Doctor principal',
            'specialty' => 'Medicina general',
        ]);
        $this->otherDoctor = Doctor::create([
            'name' => 'Doctor alterno',
            'specialty' => 'Pediatría',
        ]);
    }

    public function test_creation_rejects_overlap_but_allows_another_doctor_and_cancelled_slots(): void
    {
        $appointment = $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 10:00:00',
            '2030-01-10 11:00:00'
        ))->assertCreated();

        $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 10:30:00',
            '2030-01-10 11:30:00'
        ))->assertStatus(409)->assertExactJson([
            'message' => 'El doctor ya tiene una cita en ese horario.',
            'error' => 'SCHEDULE_CONFLICT',
        ]);

        $this->postJson('/api/citas', $this->payload(
            $this->otherDoctor,
            '2030-01-10 10:00:00',
            '2030-01-10 11:00:00'
        ))->assertCreated();

        $appointmentId = $appointment->json('id');

        $this->patchJson("/api/citas/{$appointmentId}/estado", [
            'status' => Appointment::STATUS_CANCELLED,
        ])->assertOk()->assertJsonPath('status', Appointment::STATUS_CANCELLED);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 10:00:00',
            '2030-01-10 11:00:00'
        ))->assertCreated();
    }

    public function test_rescheduling_rejects_an_occupied_slot_and_ignores_the_same_appointment(): void
    {
        $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 10:00:00',
            '2030-01-10 11:00:00'
        ))->assertCreated();

        $appointment = $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 12:00:00',
            '2030-01-10 13:00:00'
        ))->assertCreated();
        $appointmentId = $appointment->json('id');

        $this->putJson("/api/citas/{$appointmentId}", [
            'start_at' => '2030-01-10 10:30:00',
            'end_at' => '2030-01-10 11:30:00',
        ])->assertStatus(409)->assertJsonPath('error', 'SCHEDULE_CONFLICT');

        $this->putJson("/api/citas/{$appointmentId}", [
            'start_at' => '2030-01-10 12:00:00',
            'end_at' => '2030-01-10 13:00:00',
        ])->assertOk();

        $this->putJson("/api/citas/{$appointmentId}", [
            'start_at' => '2030-01-10 14:00:00',
        ])->assertUnprocessable()->assertJsonValidationErrors('end_at');
    }

    public function test_only_supported_statuses_are_accepted(): void
    {
        $appointment = $this->postJson('/api/citas', $this->payload(
            $this->doctor,
            '2030-01-10 14:00:00',
            '2030-01-10 15:00:00'
        ))->assertCreated();
        $url = '/api/citas/'.$appointment->json('id').'/estado';

        foreach (Appointment::STATUSES as $status) {
            $this->patchJson($url, ['status' => $status])
                ->assertOk()
                ->assertJsonPath('status', $status);
        }

        $this->patchJson($url, ['status' => 'desconocida'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    private function payload(Doctor $doctor, string $startAt, string $endAt): array
    {
        return [
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctor->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'reason' => 'Consulta de prueba',
        ];
    }
}
