<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTests extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Pratik Malik',
            'email' => 'pratik@mail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'message'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'pratik@mail.com']);
    }

    /** @test */
    public function registration_fails_with_invalid_data()
    {
        // Missing email and password
        $response = $this->postJson('/api/register', [
            'name' => 'Pratik Malik',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'pratik@mail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pratik@mail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'token'],
                'message'
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'pratik@mail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pratik@mail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }
}
