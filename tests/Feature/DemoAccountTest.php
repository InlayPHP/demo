<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Inlay\Media\Models\MediaAsset;
use Tests\TestCase;

class DemoAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_account_is_seeded_with_the_documented_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $user = User::query()
            ->where('email', config('demo.user.email'))
            ->sole();

        $this->assertSame(config('demo.user.name'), $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check(config('demo.user.password'), $user->password));

        $asset = MediaAsset::query()->where('file_name', 'welcome-to-inlay.txt')->sole();
        $this->assertTrue(Storage::disk($asset->disk())->exists($asset->path()));
    }

    public function test_demo_credentials_are_available_on_the_welcome_and_login_pages(): void
    {
        $credentials = [
            'email' => config('demo.user.email'),
            'password' => config('demo.user.password'),
        ];

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->where('demoCredentials.email', $credentials['email'])
                ->where('demoCredentials.password', $credentials['password']));

        $this->get(route('inlay.admin.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/auth/login')
                ->where('inlayPanel.contract', 'inlay.panels.v1')
                ->where('inlayPanel.path', '/admin')
                ->where('demoCredentials.email', $credentials['email'])
                ->where('demoCredentials.password', $credentials['password']));
    }

    public function test_seeded_demo_account_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post(route('inlay.admin.authenticate'), [
            'email' => config('demo.user.email'),
            'password' => config('demo.user.password'),
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }
}
