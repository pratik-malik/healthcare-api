<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AppointmentController extends BaseController
{
    /**
     * Display a paginated list of appointments for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {

            $query = Appointment::query()->with('professional', 'user')
                ->where('user_id', $request->user()->id)
                ->orderBy('appointment_start_time', 'desc');

            $appointments = $query->paginate(20);
            $meta = [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ];

            return $this->sendResponse(AppointmentResource::collection($appointments), 'Appointments retrieved successfully', $meta);
        } catch (Throwable $e) {
            return $this->sendError('Unable to fetch appointments', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Book a new appointment for the authenticated user.
     *
     * Validates requested start and end times, checks for overlaps with
     * existing professional or user appointments, and creates the appointment if valid.
     *
     * @param  \App\Http\Requests\BookAppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(BookAppointmentRequest $request)
    {
        try {
            $data = $request->validated();

            $start = Carbon::parse($data['appointment_start_time']);
            $end = Carbon::parse($data['appointment_end_time']);

            if ($end->lessThanOrEqualTo($start)) {
                return $this->sendError('Please check the appointment times', [], 422);
            }

            // check existing bookings for professional
            $overlap = Appointment::forProfessionalBetween(
                $data['healthcare_professional_id'],
                $start,
                $end
            )->exists();

            if ($overlap) {
                return $this->sendError('The professional is already booked during this time', [], 422);
            }

            // check existing bookings for user
            $userOverlap = Appointment::where('user_id', $request->user()->id)
                ->where('status', 'booked')
                ->where('appointment_start_time', '<', $end)
                ->where('appointment_end_time', '>', $start)
                ->exists();

            if ($userOverlap) {
                return $this->sendError('You already have an appointment during this time', [], 422);
            }

            $appointment = DB::transaction(function () use ($request, $data, $start, $end) {
                return Appointment::create([
                    'user_id' => $request->user()->id,
                    'healthcare_professional_id' => $data['healthcare_professional_id'],
                    'appointment_start_time' => $start,
                    'appointment_end_time' => $end,
                    'status' => 'booked',
                ]);
            });

            return $this->sendResponse(new AppointmentResource($appointment->load('professional')), 'Appointment created successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to create appointment', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show details of a specific appointment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Appointment $appointment)
    {
        if ($appointment->user_id !== $request->user()->id) {
            return $this->sendError('You can only view your own appointments', [], 403);
        }

        try {
            return $this->sendResponse(new AppointmentResource($appointment->load('professional', 'user')), 'Appointment retrieved successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to fetch appointment details', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an appointment for the authenticated user.
     *
     * Cancels only if:
     * - The appointment belongs to the user.
     * - The start time is more than 24 hours away.
     * - The appointment is currently booked.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        try {
            if ($appointment->user_id !== $request->user()->id) {
                return $this->sendError(
                    'You can only cancel your own appointments',
                    [],
                    403
                );
            }
            $now = Carbon::now();
            $start = Carbon::parse($appointment->appointment_start_time);

            if ($start->lessThan($now->addDay())) {
                return $this->sendError('Cancellation not allowed within 24 hours of the appointment time', [], 422);
            }

            if ($appointment->status !== 'booked') {
                return $this->sendError('This appointment is already cancelled or completed', [], 422);
            }

            $appointment->status = 'cancelled';
            $appointment->save();

            return $this->sendResponse(new AppointmentResource($appointment), 'Appointment cancelled successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to cancel appointment', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark an appointment as completed.
     *
     * Can only be done if:
     * - The appointment belongs to the user.
     * - The appointment status is currently booked.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(Request $request, Appointment $appointment)
    {
        try {
            if ($appointment->user_id !== $request->user()->id) {
                return $this->sendError('You can only complete your own appointments', [], 403);
            }

            if ($appointment->status !== 'booked') {
                return $this->sendError('Only active appointments can be marked completed', [], 422);
            }

            $appointment->status = 'completed';
            $appointment->save();

            return $this->sendResponse(new AppointmentResource($appointment), 'Appointment marked as completed successfully');
        } catch (Throwable $e) {
            return $this->sendError('Unable to complete appointment', ['error' => $e->getMessage()], 500);
        }
    }
}
