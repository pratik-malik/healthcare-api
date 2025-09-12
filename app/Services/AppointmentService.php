<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Repositories\AppointmentRepository;

class AppointmentService
{
    protected AppointmentRepository $repo;

    public function __construct(AppointmentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listUserAppointments($user, $perPage = 20)
    {
        return $this->repo->getUserAppointments($user->id, $perPage);
    }

    public function createAppointment($user, array $data)
    {
        $start = Carbon::parse($data['appointment_start_time']);
        $end = Carbon::parse($data['appointment_end_time']);

        if ($end->lessThanOrEqualTo($start)) {
            throw new \InvalidArgumentException('Please check the appointment times');
        }

        if ($this->repo->professionalHasOverlap($data['healthcare_professional_id'], $start, $end)) {
            throw new \DomainException('The professional is already booked during this time');
        }

        if ($this->repo->userHasOverlap($user->id, $start, $end)) {
            throw new \DomainException('You already have an appointment during this time');
        }

        return $this->repo->create([
            'user_id' => $user->id,
            'healthcare_professional_id' => $data['healthcare_professional_id'],
            'appointment_start_time' => $start,
            'appointment_end_time' => $end,
            'status' => 'booked',
        ]);
    }

    public function cancelAppointment($user, Appointment $appointment)
    {
        if ($appointment->user_id !== $user->id) {
            throw new \DomainException('You can only cancel your own appointments', 403);
        }

        $now = Carbon::now();
        $start = Carbon::parse($appointment->appointment_start_time);

        if ($start->lessThan($now->addDay())) {
            throw new \DomainException('Cancellation not allowed within 24 hours of the appointment time');
        }

        if ($appointment->status !== 'booked') {
            throw new \DomainException('This appointment is already cancelled or completed');
        }

        $appointment->status = 'cancelled';
        $appointment->save();

        return $appointment;
    }

    public function completeAppointment($user, Appointment $appointment)
    {
        if ($appointment->user_id !== $user->id) {
            throw new \DomainException('You can only complete your own appointments', 403);
        }

        if ($appointment->status !== 'booked') {
            throw new \DomainException('Only active appointments can be marked completed');
        }

        $appointment->status = 'completed';
        $appointment->save();

        return $appointment;
    }
}
