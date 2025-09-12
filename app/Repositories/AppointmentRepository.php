<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AppointmentRepository
{
    public function getUserAppointments($userId, $perPage = 20): LengthAwarePaginator
    {
        return Appointment::with('professional', 'user')
            ->where('user_id', $userId)
            ->orderBy('appointment_start_time', 'desc')
            ->paginate($perPage);
    }

    public function professionalHasOverlap($professionalId, $start, $end): bool
    {
        return Appointment::forProfessionalBetween($professionalId, $start, $end)->exists();
    }

    public function userHasOverlap($userId, $start, $end): bool
    {
        return Appointment::where('user_id', $userId)
            ->where('status', 'booked')
            ->where('appointment_start_time', '<', $end)
            ->where('appointment_end_time', '>', $start)
            ->exists();
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }
}