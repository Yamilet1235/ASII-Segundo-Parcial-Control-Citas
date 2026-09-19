<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public const STATUS_PENDING = 'pendiente';

    public const STATUS_CONFIRMED = 'confirmada';

    public const STATUS_CANCELLED = 'cancelada';

    public const STATUS_ATTENDED = 'atendida';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_ATTENDED,
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'start_at',
        'end_at',
        'reason',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
