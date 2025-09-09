<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthcareProfessionalController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('get-profile', [AuthController::class, 'user']);
    Route::get('professionals', [HealthcareProfessionalController::class, 'index']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('appointments', AppointmentController::class)->only([
        'index', 'store', 'show'
    ]);

    // Extra actions for appointments
    Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::patch('appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
});

