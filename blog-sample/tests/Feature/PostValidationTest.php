<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_post_can_be_created_with_valid_data()
    {
        $data = [
            'title' => 'Valid Title',
            'content' => 'Valid content for the post.',
        ];

        $response = $this->post(route('posts.store'), $data);

        $post = Post::latest('id')->first();
        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('posts', $data);
    }

    /** @test */
    public function a_post_cannot_be_created_with_a_title_longer_than_64_characters()
    {
        $data = [
            'title' => str_repeat('a', 65),
            'content' => 'Valid content.',
        ];

        $response = $this->post(route('posts.store'), $data);

        $response->assertSessionHasErrors('title');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_created_with_content_longer_than_1024_characters()
    {
        $data = [
            'title' => 'Valid Title',
            'content' => str_repeat('a', 1025),
        ];

        $response = $this->post(route('posts.store'), $data);

        $response->assertSessionHasErrors('content');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_created_without_a_title()
    {
        $data = [
            'content' => 'Valid content.',
        ];

        $response = $this->post(route('posts.store'), $data);

        $response->assertSessionHasErrors('title');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_created_without_content()
    {
        $data = [
            'title' => 'Valid Title',
        ];

        $response = $this->post(route('posts.store'), $data);

        $response->assertSessionHasErrors('content');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_can_be_updated_with_valid_data()
    {
        $post = Post::factory()->create();
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content for the post.',
        ];

        $response = $this->put(route('posts.update', $post), $data);

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('posts', array_merge(['id' => $post->id], $data));
    }

    /** @test */
    public function a_post_cannot_be_updated_with_a_title_longer_than_64_characters()
    {
        $post = Post::factory()->create();
        $data = [
            'title' => str_repeat('a', 65),
            'content' => 'Valid content.',
        ];

        $response = $this->put(route('posts.update', $post), $data);

        $response->assertSessionHasErrors('title');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_updated_with_content_longer_than_1024_characters()
    {
        $post = Post::factory()->create();
        $data = [
            'title' => 'Valid Title',
            'content' => str_repeat('a', 1025),
        ];

        $response = $this->put(route('posts.update', $post), $data);

        $response->assertSessionHasErrors('content');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_updated_without_a_title()
    {
        $post = Post::factory()->create();
        $data = [
            'content' => 'Valid content.',
        ];

        $response = $this->put(route('posts.update', $post), $data);

        $response->assertSessionHasErrors('title');
        $response->assertRedirect();
    }

    /** @test */
    public function a_post_cannot_be_updated_without_content()
    {
        $post = Post::factory()->create();
        $data = [
            'title' => 'Valid Title',
        ];

        $response = $this->put(route('posts.update', $post), $data);

        $response->assertSessionHasErrors('content');
        $response->assertRedirect();
    }
}
