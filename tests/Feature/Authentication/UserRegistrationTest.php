<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A test to ensure user can register with unique email.
     */
    public function test_user_can_register_using_unique_email(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->postJson(route('api.register'), $payload);
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'access_token'
            ]
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * A test to ensure user cannot register with an existing email.
     */
    public function test_user_cannot_register_using_existing_email(): void
    {
        $existingUser = User::factory(1, [
            'email' => 'test@example.com'
        ])->create()->first();

        $payload = [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->postJson(route('api.register'), $payload);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * A test to ensure user cannot register if password and confirmation do not match.
     */
    public function cannot_register_if_password_and_confirmation_do_not_match(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password',
        ];
        $response = $this->postJson(route('api.register'), $payload);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['password']);
    }
}
