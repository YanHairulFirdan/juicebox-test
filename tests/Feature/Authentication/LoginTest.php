<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A test to ensure user cannot login with unregistered email.
     */
    public function test_cannot_login_using_unregistered_email(): void
    {
        $payload = [
            'email' => 'unregistered@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson(route('api.login'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * A test to ensure user can login with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $password = $this->faker->password();

        $user = \App\Models\User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson(route('api.login'), $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                ],
            ]);
    }

    /**
     * A test to ensure user cannot login with incorrect password.
     */
    public function test_cannot_login_with_incorrect_password(): void
    {
        $password = $this->faker->password();
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt($password),
        ]);
        $payload = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];
        $response = $this->postJson(route('api.login'), $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
