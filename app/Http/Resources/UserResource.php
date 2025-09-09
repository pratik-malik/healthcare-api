<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected $token;

    // Allow passing token into the resource
    public function withToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
        ];

        // Only include token if it was explicitly set
        if ($this->token) {
            $data['token'] = $this->token;
        }

        return $data;
    }
}
