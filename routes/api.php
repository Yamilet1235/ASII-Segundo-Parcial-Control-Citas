<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/citas', [AppointmentController::class, 'index']);
Route::post('/citas', [AppointmentController::class, 'store']);
Route::get('/citas/{id}', [AppointmentController::class, 'show']);
Route::put('/citas/{id}', [AppointmentController::class, 'update']);
Route::patch('/citas/{id}/estado', [
    AppointmentController::class,
    'changeStatus',
]);

Route::get('/doctores', [DoctorController::class, 'index']);
Route::get('/pacientes', [PatientController::class, 'index']);