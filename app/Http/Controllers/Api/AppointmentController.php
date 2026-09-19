<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $service
    ) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->service->getAll($request->only([
                'doctor_id',
                'patient_id',
                'from',
                'to',
            ]))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(Appointment::STATUSES)],
        ]);

        $appointment = $this->service->create($data);

        return response()->json($appointment, 201);
    }

    public function show(int $id)
    {
        return response()->json(
            $this->service->find($id)
        );
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'patient_id' => ['sometimes', 'integer', 'exists:patients,id'],
            'doctor_id' => ['sometimes', 'integer', 'exists:doctors,id'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date'],
            'reason' => ['sometimes', 'string', 'max:255'],
        ]);

        return response()->json(
            $this->service->update($id, $data)
        );
    }

    public function changeStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(Appointment::STATUSES),
            ],
        ]);

        return response()->json(
            $this->service->changeStatus($id, $data['status'])
        );
    }
}
