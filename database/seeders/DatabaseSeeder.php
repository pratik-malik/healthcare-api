<?php

namespace Database\Seeders;

use App\Models\HealthcareProfessional;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        HealthcareProfessional::factory()->count(10)->create();
        User::factory()->count(10)->create();
        $this->call(UserAppintmentsSeeder::class);
    }
}
