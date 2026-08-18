<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlogResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_manage_blog_posts_with_the_resource(): void
    {
        $administrator = User::factory()->create();
        $this->actingAs($administrator);

        $this->get(route('inlay.admin.blogs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/resource/index')
                ->where('table.contract', 'inlay.tables.v1')
                ->where('resource.slug', 'blogs'));

        $this->get(route('inlay.admin.blogs.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/resource/form')
                ->where('form.contract', 'inlay.forms.v1')
                ->where('form.method', 'post'));

        $this->post(route('inlay.admin.blogs.store'), [
            'title' => '  A New Inlay Post  ',
            'slug' => 'A New Inlay Post',
            'status' => 'draft',
            'excerpt' => 'A short introduction.',
            'body' => 'The body of the new post.',
            'featured' => true,
        ])->assertRedirect('/admin/blogs');

        $post = Blog::query()->where('slug', 'a-new-inlay-post')->firstOrFail();
        $this->assertSame('A New Inlay Post', $post->title);
        $this->assertTrue($post->featured);

        $this->get(route('inlay.admin.blogs.edit', $post))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/resource/form')
                ->where('form.method', 'patch')
                ->where('form.data.title', 'A New Inlay Post'));

        $this->patch(route('inlay.admin.blogs.update', $post), [
            'title' => 'Updated Inlay Post',
            'slug' => 'updated-inlay-post',
            'status' => 'published',
            'excerpt' => 'Updated excerpt.',
            'body' => 'Updated body.',
            'published_at' => now()->toDateTimeString(),
            'featured' => false,
        ])->assertRedirect('/admin/blogs');

        $this->assertDatabaseHas('blogs', [
            'id' => $post->getKey(),
            'title' => 'Updated Inlay Post',
            'slug' => 'updated-inlay-post',
            'status' => 'published',
        ]);

        $this->delete(route('inlay.admin.blogs.destroy', $post))
            ->assertRedirect('/admin/blogs');

        $this->assertDatabaseMissing('blogs', ['id' => $post->getKey()]);
    }

    public function test_guests_cannot_open_the_blog_resource(): void
    {
        $this->get(route('inlay.admin.blogs.index'))
            ->assertRedirect(route('inlay.admin.login'));
    }
}
