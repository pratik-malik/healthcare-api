<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\HealthcareProfessional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected HealthcareProfessional $professional;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create a healthcare professional
        $this->professional = HealthcareProfessional::factory()->create();
    }

    #[Test]
    public function user_can_list_their_appointments()
    {
        Appointment::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
        ]);

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    #[Test]
    public function user_can_create_an_appointment()
    {
        $start = now()->addDays(2)->format('Y-m-d H:i:s');
        $end = now()->addDays(2)->addHour()->format('Y-m-d H:i:s');

        $response = $this->postJson('/api/appointments', [
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => $start,
            'appointment_end_time' => $end,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'status']]);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
        ]);
    }

    #[Test]
    public function user_can_view_a_single_appointment()
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
        ]);

        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'status', 'appointment_start_time', 'appointment_end_time']]);
    }

    #[Test]
    public function user_can_cancel_their_appointment()
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => now()->addDays(2),
            'appointment_end_time' => now()->addDays(2)->addHour(),
            'status' => 'booked',
        ]);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/cancel");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Appointment cancelled successfully']);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    #[Test]
    public function user_can_complete_their_appointment()
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => now()->subDays(1),
            'appointment_end_time' => now()->subDays(1)->addHour(),
            'status' => 'booked',
        ]);

        $response = $this->patchJson("/api/appointments/{$appointment->id}/complete");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Appointment marked as completed successfully']);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function user_cannot_create_overlapping_appointment_for_same_professional()
    {
        $start = now()->addDays(3);
        $end = now()->addDays(3)->addHour();

        // Existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => $start,
            'appointment_end_time' => $end,
            'status' => 'booked',
        ]);

        $response = $this->postJson('/api/appointments', [
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => Carbon::parse($start)->addMinutes(30)->format('Y-m-d H:i'),
            'appointment_end_time'   => Carbon::parse($end)->addMinutes(30)->format('Y-m-d H:i'),
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    #[Test]
    public function user_cannot_create_overlapping_appointment_for_self()
    {
        // normalize to minute precision
        $start = now()->addDays(4)->setSeconds(0);
        $end   = $start->copy()->addHour();

        // Existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'healthcare_professional_id' => $this->professional->id,
            'appointment_start_time' => $start,
            'appointment_end_time'   => $end,
            'status' => 'booked',
        ]);

        $anotherProfessional = HealthcareProfessional::factory()->create();

        $response = $this->postJson('/api/appointments', [
            'healthcare_professional_id' => $anotherProfessional->id,
            'appointment_start_time' => $start->copy()->addMinutes(15)->format('Y-m-d H:i'),
            'appointment_end_time'   => $end->copy()->addMinutes(15)->format('Y-m-d H:i'),
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }
}
