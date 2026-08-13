<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_navigation_groups_standalone_demos_in_new_tabs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('inlay.admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('inlayPanel.navigationItems', function ($items): bool {
                    $items = collect($items)->keyBy('name');

                    return $items['account-settings']['url'] === '/admin/settings/account';
                })
                ->where('inlayPanel.navigationGroups', function ($groups): bool {
                    $examples = collect($groups)->firstWhere('name', 'examples');
                    $items = collect($examples['items'] ?? [])->keyBy('name');
                    $resources = collect($groups)->firstWhere('name', 'resources');

                    return $items['forms-demo']['openInNewTab'] === true
                        && $items['tables-demo']['openInNewTab'] === true
                        && $items['source']['openInNewTab'] === true
                        && collect($resources['items'] ?? [])->contains(fn (array $item): bool => $item['name'] === 'resource-users'
                            && $item['url'] === '/admin/users')
                        && collect($resources['items'] ?? [])->contains(fn (array $item): bool => $item['name'] === 'resource-blogs'
                            && $item['url'] === '/admin/blogs');
                }));
    }

    public function test_authenticated_administrator_can_manage_users_with_the_resource(): void
    {
        $administrator = User::factory()->create();
        $this->actingAs($administrator);

        $this->get(route('inlay.admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/index')
                ->where('table.contract', 'inlay.tables.v1')
                ->where('resource.slug', 'users'));

        $this->get(route('inlay.admin.users.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/form')
                ->where('form.contract', 'inlay.forms.v1')
                ->where('form.method', 'post'));

        $this->post(route('inlay.admin.users.store'), [
            'name' => '  New User  ',
            'email' => 'NEW.USER@EXAMPLE.COM',
            'password' => 'new-user-password',
        ])->assertRedirect('/admin/users');

        $managed = User::query()->where('email', 'new.user@example.com')->firstOrFail();
        $this->assertSame('New User', $managed->name);
        $this->assertTrue(Hash::check('new-user-password', $managed->password));

        $password = $managed->password;

        $this->patch(route('inlay.admin.users.update', $managed), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => '',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $managed->getKey(),
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);
        $this->assertSame($password, $managed->fresh()->password);

        $this->delete(route('inlay.admin.users.destroy', $managed))
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $managed->getKey()]);
    }

    public function test_administrator_cannot_delete_their_own_account(): void
    {
        $administrator = User::factory()->create();

        $this->actingAs($administrator)
            ->delete(route('inlay.admin.users.destroy', $administrator))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $administrator->getKey()]);
    }
}
