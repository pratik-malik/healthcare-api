<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+1 month');

        return [
            'appointment_start_time' => $start->format('Y-m-d H:i'),
            'appointment_end_time'   => (clone $start)->modify('+60 minutes')->format('Y-m-d H:i'),
            'status' => $this->faker->randomElement(['booked', 'completed', 'cancelled']),
        ];
    }
}
