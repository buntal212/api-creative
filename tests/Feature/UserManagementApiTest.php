<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_other_users(): void
    {
        $admin = User::factory()->create();

        $createdUser = $this->actingAs($admin)->postJson('/api/users', [
            'name' => 'Admin Toko',
            'username' => 'admin-toko',
            'email' => 'admin@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $createdUser
            ->assertCreated()
            ->assertJsonPath('username', 'admin-toko')
            ->assertJsonMissingPath('password');

        $userId = $createdUser->json('id');

        $this->actingAs($admin)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['id' => $userId]);

        $this->actingAs($admin)
            ->putJson("/api/users/{$userId}", [
                'name' => 'Admin Penjualan',
                'username' => 'admin-toko',
                'email' => 'admin@example.test',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Admin Penjualan');

        $this->actingAs($admin)
            ->deleteJson("/api/users/{$userId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_authenticated_user_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/users/{$user->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Akun yang sedang digunakan tidak dapat dihapus.');
    }
}
