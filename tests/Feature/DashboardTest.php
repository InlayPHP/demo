<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_panel_login_page()
    {
        $response = $this->get(route('inlay.admin.dashboard'));
        $response->assertRedirect(route('inlay.admin.login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('inlay.admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/dashboard')
                ->where('inlayPanel.brandName', 'Inlay Demo')
                ->where('inlayPanel.path', '/admin')
                ->where('inlayPage.type', 'dashboard')
                ->where('inlayWidgets.contract', 'inlay.widget-dashboard.v1')
                ->where('inlayWidgets.widgets', function ($widgets): bool {
                    return collect($widgets)->pluck('name')->all() === [
                        'overview',
                        'content-activity',
                        'recent-posts',
                        'recent-orders',
                        'people-workload',
                    ];
                }));
    }

    public function test_media_manager_is_part_of_the_default_panel()
    {
        $user = User::factory()->create();

        $this->get('/admin/media')
            ->assertRedirect(route('inlay.admin.login'));

        $this->actingAs($user)
            ->get('/admin/media')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay-media-manager/index')
                ->where('inlayPanel.path', '/admin')
                ->where('media.contract', 'inlay.media-manager.v1'));
    }

    public function test_starter_kit_routes_are_not_registered()
    {
        foreach ([
            'login',
            'login.store',
            'register',
            'password.request',
            'dashboard',
            'profile.edit',
            'security.edit',
            'appearance.edit',
        ] as $name) {
            $this->assertNull(app('router')->getRoutes()->getByName($name));
        }

        foreach (['/login', '/register', '/dashboard', '/settings/profile', '/settings/security'] as $uri) {
            $this->get($uri)->assertNotFound();
        }
    }

    public function test_panel_account_settings_and_logout_are_available()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('inlay.admin.account.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inlay/account-settings')
                ->where('inlayPage.type', 'account-settings')
                ->has('profileForm')
                ->has('passwordForm'));

        $this->post(route('inlay.admin.logout'))
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }
}
