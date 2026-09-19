<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
