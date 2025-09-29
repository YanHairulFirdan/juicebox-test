<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     */
    public function test_can_retrieve_posts_without_login(): void
    {
        Post::factory(50)->create();

        $response = $this->get(route('api.posts.index'));
        $response->assertStatus(200);
    }

    public function test_can_search_posts_by_title(): void
    {
        Post::factory()->create(['title' => 'Unique Title']);
        Post::factory(10)->create();

        $response = $this->get(route('api.posts.index', ['search' => 'Unique Title']));
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_search_posts_by_body(): void
    {
        Post::factory()->create(['body' => 'Unique Body Content']);
        Post::factory(10)->create();

        $response = $this->get(route('api.posts.index', ['search' => 'Unique Body Content']));
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_search_posts_by_user_name(): void
    {
        \App\Models\User::factory()
            ->has(Post::factory()->count(3))
            ->create(['name' => 'UniqueUserName']);

        Post::factory(10)->create();

        $response = $this->get(route('api.posts.index', ['search' => 'UniqueUserName']));
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_pagination_works(): void
    {
        Post::factory(25)->create();

        $response = $this->get(route('api.posts.index'));
        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 25);
        $response->assertJsonPath('meta.current_page', 1);

        $response = $this->get(route('api.posts.index', ['page' => 2]));
        $response->assertStatus(200);
        $response->assertJsonPath('meta.current_page', 2);
    }

    public function test_can_retrieve_single_post(): void
    {
        $post = Post::factory()->create();
        $response = $this->getJson(route('api.posts.show', $post->id));
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $post->id);
        $response->assertJsonPath('data.title', $post->title);
        $response->assertJsonPath('data.body', $post->body);
        $response->assertJsonPath('data.user.id', $post->user_id);
        $response->assertJsonPath('data.user.name', $post->user->name);
        $response->assertJsonPath('data.user.email', $post->user->email);
    }

    public function test_retrieve_non_existent_post(): void
    {
        $response = $this->getJson(route('api.posts.show', 9999));
        $response->assertStatus(404);
    }

    public function test_cannot_create_post_without_authentication(): void
    {
        $postData = [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
        ];

        $response = $this->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(401);
    }

    public function test_can_create_data_with_authentication(): void
    {
        $user = \App\Models\User::factory()->create();
        $postData = [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
        ];

        $response = $this->actingAs($user)->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(201);
        $response->assertJsonPath('data.title', $postData['title']);
        $response->assertJsonPath('data.body', $postData['body']);
        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.user.name', $user->name);
        $response->assertJsonPath('data.user.email', $user->email);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'body' => $postData['body'],
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_create_post_with_invalid_data(): void
    {
        $user = \App\Models\User::factory()->create();

        $postData = [
            'body' => $this->faker->paragraph,
        ];
        $response = $this->actingAs($user)->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');

        $postData = [
            'title' => $this->faker->words(100, true),
            'body' => $this->faker->paragraph,
        ];
        $response = $this->actingAs($user)->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');

        $postData = [
            'title' => $this->faker->sentence,
        ];
        $response = $this->actingAs($user)->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');

        $postData = [
            'title' => $this->faker->sentence,
            'body' => $this->faker->words(10000, true),
        ];
        $response = $this->actingAs($user)->postJson(route('api.posts.store'), $postData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_cannot_update_post_without_authentication(): void
    {
        $post = Post::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
        ];

        $response = $this->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(401);
    }

    public function test_cannot_update_post_of_another_user(): void
    {
        $post = Post::factory()->create();
        $otherUser = \App\Models\User::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
        ];

        $response = $this->actingAs($otherUser)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(403);
    }

    public function test_can_update_own_post(): void
    {
        $user = \App\Models\User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
        ];

        $response = $this->actingAs($user)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $post->id);
        $response->assertJsonPath('data.title', $updateData['title']);
        $response->assertJsonPath('data.body', $updateData['body']);
        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.user.name', $user->name);
        $response->assertJsonPath('data.user.email', $user->email);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'body' => $updateData['body'],
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_update_post_with_invalid_data(): void
    {
        $user = \App\Models\User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'body' => 'Updated body content.',
        ];
        $response = $this->actingAs($user)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');

        $updateData = [
            'title' => 'T' . str_repeat('o', 255),
            'body' => 'Updated body content.',
        ];
        $response = $this->actingAs($user)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');

        $updateData = [
            'title' => 'Updated Title',
        ];
        $response = $this->actingAs($user)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');

        $updateData = [
            'title' => 'Updated Title',
            'body' => str_repeat('B', 65536),
        ];
        $response = $this->actingAs($user)->patchJson(route('api.posts.update', $post->id), $updateData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_cannot_update_non_existent_post(): void
    {
        $user = \App\Models\User::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content.',
        ];

        $response = $this->actingAs($user)->patchJson(route('api.posts.update', 9999), $updateData);
        $response->assertStatus(404);
    }

    public function test_cannot_delete_post_without_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson(route('api.posts.destroy', $post->id));
        $response->assertStatus(401);
    }

    public function test_cannot_delete_post_of_another_user(): void
    {
        $post = Post::factory()->create();
        $otherUser = \App\Models\User::factory()->create();

        $response = $this->actingAs($otherUser)->deleteJson(route('api.posts.destroy', $post->id));
        $response->assertStatus(403);
    }

    public function test_can_delete_own_post(): void
    {
        $user = \App\Models\User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.posts.destroy', $post->id));
        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
