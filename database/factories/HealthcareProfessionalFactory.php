<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthcareProfessional>
 */
class HealthcareProfessionalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'specialty' => $this->faker->randomElement(['General Practitioner', 'Dentist', 'Dermatologist', 'Cardiologist', 'Pediatrician'])
        ];
    }
}
