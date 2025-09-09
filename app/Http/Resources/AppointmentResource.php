<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'professional' => new HealthcareProfessionalResource($this->whenLoaded('professional')),
            'appointment_start_time' => $this->formatted_start_time,
            'appointment_end_time' => $this->formatted_end_time,
            'status' => $this->status,
            'created_at' => $this->formatted_created_at,
        ];
    }
}
