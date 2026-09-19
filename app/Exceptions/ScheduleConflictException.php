<?php

namespace App\Exceptions;

use RuntimeException;

class ScheduleConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El doctor ya tiene una cita en ese horario.');
    }
}
