<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_access_their_profile_with_a_sanctum_token(): void
    {
        $user = User::factory()->create([
            'name' => 'admin-udumbara',
            'username' => 'admin-udumbara',
            'password' => Hash::make('rahasia123'),
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'admin-udumbara',
            'password' => 'rahasia123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($response->json('token'))
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_login_rejects_an_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('rahasia123'),
        ]);

        $this->postJson('/api/login', [
            'username' => 'username-yang-salah',
            'password' => 'password-salah',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Username atau password tidak valid.');
    }
}
