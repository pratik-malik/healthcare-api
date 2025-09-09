<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\HealthcareProfessional;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserAppintmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // pick random user and assign random appointments
        $users = User::all();
        $professionals = HealthcareProfessional::all();

        foreach ($users as $user) {
            foreach (range(1, 3) as $i) {
                Appointment::factory()->create([
                    'user_id' => $user->id,
                    'healthcare_professional_id' => $professionals->random()->id,
                ]);
            }
        }
    }
}
