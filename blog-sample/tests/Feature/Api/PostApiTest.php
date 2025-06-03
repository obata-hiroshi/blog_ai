<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserAndAuthenticate()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_unauthenticated_user_cannot_access_posts_index(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertUnauthorized();
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $response->assertOk()
                 ->assertJsonStructure(['token']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password123')]);
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertUnauthorized()
                 ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_logout_successfully(): void
    {
        $this->createUserAndAuthenticate();
        $response = $this->postJson('/api/logout');
        $response->assertOk()
                 ->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_authenticated_user_can_get_all_posts(): void
    {
        $user = $this->createUserAndAuthenticate();
        Post::factory()->count(3)->for($user)->create();

        $response = $this->getJson('/api/posts');
        $response->assertOk()
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'content', 'author_name', 'created_at', 'updated_at']
                     ],
                     'links', 'meta' // For pagination
                 ]);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = $this->createUserAndAuthenticate();
        $postData = ['title' => 'New Post Title', 'content' => 'New post content.'];

        $response = $this->postJson('/api/posts', $postData);
        $response->assertCreated() // Checks for 201 status
                 ->assertJsonFragment(['title' => 'New Post Title'])
                 ->assertJsonStructure(['data' => ['id', 'title', 'content', 'author_name']]);

        $this->assertDatabaseHas('posts', array_merge($postData, ['user_id' => $user->id]));
    }

    public function test_create_post_fails_with_validation_errors(): void
    {
        $this->createUserAndAuthenticate();
        $response = $this->postJson('/api/posts', ['title' => '']); // Missing content, empty title
        $response->assertStatus(422) // Unprocessable Entity for validation errors
                 ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_authenticated_user_can_get_single_post(): void
    {
        $user = $this->createUserAndAuthenticate();
        $post = Post::factory()->for($user)->create();

        $response = $this->getJson("/api/posts/{$post->id}");
        $response->assertOk()
                 ->assertJsonFragment(['title' => $post->title])
                 ->assertJsonStructure(['data' => ['id', 'title', 'content', 'author_name']]);
    }

    public function test_authenticated_user_can_update_own_post(): void
    {
        $user = $this->createUserAndAuthenticate();
        $post = Post::factory()->for($user)->create();
        $updatedData = ['title' => 'Updated Title', 'content' => 'Updated content.'];

        $response = $this->putJson("/api/posts/{$post->id}", $updatedData);
        $response->assertOk()
                 ->assertJsonFragment(['title' => 'Updated Title']);
        $this->assertDatabaseHas('posts', array_merge(['id' => $post->id], $updatedData));
    }

    public function test_user_cannot_update_others_post(): void
    {
        $this->createUserAndAuthenticate(); // Authenticated as user1
        $otherUser = User::factory()->create();
        $otherPost = Post::factory()->for($otherUser)->create();

        $response = $this->putJson("/api/posts/{$otherPost->id}", ['title' => 'Trying to update']);
        $response->assertForbidden(); // 403 status
    }

    public function test_update_post_fails_with_validation_errors(): void
    {
        $user = $this->createUserAndAuthenticate();
        $post = Post::factory()->for($user)->create();

        $response = $this->putJson("/api/posts/{$post->id}", ['title' => '']); // Invalid: empty title
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_authenticated_user_can_delete_own_post(): void
    {
        $user = $this->createUserAndAuthenticate();
        $post = Post::factory()->for($user)->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");
        $response->assertNoContent(); // 204 status

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $this->createUserAndAuthenticate(); // Authenticated as user1
        $otherUser = User::factory()->create();
        $otherPost = Post::factory()->for($otherUser)->create();

        $response = $this->deleteJson("/api/posts/{$otherPost->id}");
        $response->assertForbidden(); // 403 status
        $this->assertNotSoftDeleted('posts', ['id' => $otherPost->id]);
    }
}
