<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Blog\Author;
use App\Models\Blog\Category;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\Expense;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\Project;
use App\Models\Hr\Task;
use App\Models\Hr\Timesheet;
use App\Models\Shop\Brand;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Shop\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class ShowcaseResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_blog_and_hr_resources_are_php_defined_and_render_shared_pages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['products', 'customers', 'orders', 'brands', 'product-categories', 'authors', 'categories', 'departments', 'employees', 'projects', 'leave-requests', 'expenses', 'tasks', 'timesheets'] as $slug) {
            $this->get(route("inlay.admin.{$slug}.index"))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('inlay/resource/index')
                    ->where('resource.slug', $slug)
                    ->where('table.contract', 'inlay.tables.v1'));
        }
    }

    public function test_resource_detail_pages_use_the_shared_infolist_renderer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $records = [
            ['products', Product::factory()->create()],
            ['customers', Customer::factory()->create()],
            ['orders', Order::factory()->create()],
            ['brands', Brand::factory()->create()],
            ['product-categories', ProductCategory::factory()->create()],
            ['authors', Author::factory()->create()],
            ['categories', Category::factory()->create()],
            ['departments', Department::factory()->create()],
            ['employees', Employee::factory()->create()],
            ['projects', Project::factory()->create()],
            ['leave-requests', LeaveRequest::factory()->create()],
            ['expenses', Expense::factory()->create()],
            ['tasks', Task::factory()->create()],
            ['timesheets', Timesheet::factory()->create()],
        ];

        foreach ($records as [$slug, $record]) {
            $this->get(route("inlay.admin.{$slug}.view", $record))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('inlay/resource/view')
                    ->where('infolist.contract', 'inlay.infolists.v1'));
        }
    }

    public function test_every_showcase_resource_exposes_a_shared_php_form_contract(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['products', 'customers', 'orders', 'brands', 'product-categories', 'authors', 'categories', 'departments', 'employees', 'projects', 'leave-requests', 'expenses', 'tasks', 'timesheets'] as $slug) {
            $this->get(route("inlay.admin.{$slug}.create"))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('inlay/resource/form')
                    ->where('form.contract', 'inlay.forms.v1'));
        }
    }

    public function test_order_tabs_are_server_owned(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'paid']);
        $this->actingAs($user);

        $this->get(route('inlay.admin.orders.index', ['tab' => 'pending']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('tabs.active', 'pending')
                ->where('tabs.items.1.badge', 2));
    }

    public function test_product_forms_use_centralized_application_validation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('inlay.admin.products.store'), [
            'name' => 'PHP-first keyboard',
            'sku' => 'SKU-PHP-001',
            'status' => 'active',
            'price' => 129.50,
            'stock' => 18,
            'featured' => true,
            'description' => 'A seeded showcase product.',
        ])->assertRedirect('/admin/products');

        $this->assertDatabaseHas('shop_products', ['sku' => 'SKU-PHP-001', 'name' => 'PHP-first keyboard']);
    }
}
