<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'healthcare_professional_id' => ['required', 'exists:healthcare_professionals,id'],
            'appointment_start_time' => ['required', 'date', 'after:now'],
            'appointment_end_time' => ['required', 'date', 'after:appointment_start_time'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'healthcare_professional_id.required' => 'Healthcare professional is required',
            'healthcare_professional_id.exists' => 'Selected healthcare professional does not exist',
            'appointment_start_time.required' => 'Appointment start time is required',
            'appointment_start_time.date' => 'Appointment start time must be a valid date',
            'appointment_start_time.after' => 'Appointment start time must be in the future',
            'appointment_end_time.required' => 'Appointment end time is required',
            'appointment_end_time.date' => 'Appointment end time must be a valid date',
            'appointment_end_time.after' => 'Appointment end time must be after start time',
        ];
    }
}
