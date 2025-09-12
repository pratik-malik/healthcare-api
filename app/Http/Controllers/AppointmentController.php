<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentController extends BaseController
{
    protected AppointmentService $service;

    public function __construct(AppointmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a paginated list of appointments for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $appointments = $this->service->listUserAppointments($request->user());
            $meta = [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ];
            return $this->sendResponse(AppointmentResource::collection($appointments), 'Appointments retrieved successfully', $meta);
        } catch (Throwable $e) {
            Log::error('Unable to fetch appointments', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred. Please try again later.', [], 500);
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
            $appointment = $this->service->createAppointment($request->user(), $request->validated());
            return $this->sendResponse(new AppointmentResource($appointment->load('professional')), 'Appointment created successfully');
        } catch (\DomainException|\InvalidArgumentException $e) {
            // Only show safe, user-facing messages for known exceptions
            return $this->sendError($e->getMessage(), [], 422);
        } catch (Throwable $e) {
            // Log internal error, show generic message to user
            Log::error('Unable to create appointment', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred. Please try again later.', [], 500);
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
            Log::error('Unable to fetch appointment details', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred. Please try again later.', [], 500);
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
            $appointment = $this->service->cancelAppointment($request->user(), $appointment);
            return $this->sendResponse(new AppointmentResource($appointment), 'Appointment cancelled successfully');
        } catch (\DomainException $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 422);
        } catch (Throwable $e) {
            Log::error('Unable to cancel appointment', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred. Please try again later.', [], 500);
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
            $appointment = $this->service->completeAppointment($request->user(), $appointment);
            return $this->sendResponse(new AppointmentResource($appointment), 'Appointment marked as completed successfully');
        } catch (\DomainException $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 422);
        } catch (Throwable $e) {
            Log::error('Unable to complete appointment', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred. Please try again later.', [], 500);
        }
    }
}
