<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index()
    {
        return response()->json(
            Patient::orderBy('name')->get()
        );
    }
}