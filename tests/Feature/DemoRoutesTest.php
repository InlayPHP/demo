<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DemoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_links_to_each_demo_path(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_each_demo_path_is_available(): void
    {
        $this->get(route('demo.forms'))->assertOk();
        $this->get(route('demo.tables'))->assertOk();
        $this->get(route('demo.full-feature'))->assertRedirect('/admin');
    }

    public function test_form_demo_is_backed_by_an_inlay_form_page(): void
    {
        $this->get(route('demo.forms'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('demo/form')
                ->has('form')
                ->where('form.name', 'create_demo_user'),
            );
    }

    public function test_form_demo_can_create_a_user(): void
    {
        $this->post(route('demo.forms'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.test',
        ]);
    }

    public function test_table_demo_is_backed_by_an_inlay_table_page(): void
    {
        $this->get(route('demo.tables'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('demo/table')
                ->has('table')
                ->where('table.name', 'list_demo_users'),
            );
    }

    public function test_unknown_demo_paths_return_not_found(): void
    {
        $this->get('/demo/not-a-demo')->assertNotFound();
    }
}
