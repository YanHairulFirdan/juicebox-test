<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * A basic feature test example.
     */
    public function test_get_paginated_user(): void
    {
        \App\Models\User::factory(100)->create();

        $response = $this->get(route('api.user.index'));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        $this->assertCount(10, $response->json('data'));

        $response->assertStatus(200);
    }

    /** test get paginated user second page */
    public function test_get_paginated_user_second_page(): void
    {
        \App\Models\User::factory(100)->create();

        $response = $this->get(route('api.user.index', ['page' => 2]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.current_page'));

        $response->assertStatus(200);
    }

    /** test get filtered user list */
    public function test_get_filtered_user_list(): void
    {
        \App\Models\User::factory()->count(5)->create([
            'name' => 'John ' . $this->faker->lastName(),
        ]);
        \App\Models\User::factory()->count(5)->create([
            'name' => 'Doe ' . $this->faker->lastName(),
        ]);


        $response = $this->get(route('api.user.index', ['search' => 'John']));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
        $this->assertCount(5, $response->json('data'));
        foreach ($response->json('data') as $user) {
            $this->assertStringContainsString('John', $user['name']);
            // $this->assertStringContainsString('Doe', $user['name']);
        }
        $response = $this->get(route('api.user.index', ['search' => 'Doe']));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
        $this->assertCount(5, $response->json('data'));
        foreach ($response->json('data') as $user) {
            $this->assertStringContainsString('Doe', $user['name']);
        }
        $response->assertStatus(200);
    }

    /** test get single user */
    public function test_get_single_user(): void
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->get(route('api.user.show', ['user' => $user->id]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);
        $this->assertEquals($user->id, $response->json('data.id'));
        $this->assertEquals($user->name, $response->json('data.name'));
        $this->assertEquals($user->email, $response->json('data.email'));
    }

    /** test get single user not found */
    public function test_get_single_user_not_found(): void
    {
        $response = $this->get(route('api.user.show', ['user' => 9999]));
        $response->assertStatus(404);
    }
}
