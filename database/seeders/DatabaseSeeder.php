<?php

namespace Database\Seeders;

use App\Models\Blog;
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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inlay\Media\Models\MediaAsset;
use Inlay\Media\Models\MediaFolder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => config('demo.user.email'),
        ], [
            'name' => config('demo.user.name'),
            'email_verified_at' => now(),
            'password' => Hash::make(config('demo.user.password')),
        ]);

        $posts = [
            [
                'slug' => 'welcome-to-inlay',
                'title' => 'Welcome to Inlay',
                'status' => 'published',
                'excerpt' => 'A calm Laravel administration experience built from PHP contracts and Inertia pages.',
                'body' => "This seeded post demonstrates a complete Inlay resource.\n\nEdit it, publish it, or use it as the starting point for your own content model.",
                'published_at' => now()->subDays(2),
                'featured' => true,
            ],
            [
                'slug' => 'forms-and-tables-together',
                'title' => 'Forms and tables together',
                'status' => 'published',
                'excerpt' => 'The Blog resource uses the same server-authored form and table contracts as the rest of the panel.',
                'body' => "Inlay keeps the schema in Laravel while React renders the versioned contract.\n\nThat makes validation, authorization, and layout easy to test.",
                'published_at' => now()->subDay(),
                'featured' => false,
            ],
            [
                'slug' => 'ideas-for-your-next-resource',
                'title' => 'Ideas for your next resource',
                'status' => 'draft',
                'excerpt' => 'Try adding categories, authors, or a relation manager to this small example.',
                'body' => "This draft is intentionally simple so you can extend it in the dashboard.\n\nAdd fields, actions, filters, or policies as your application grows.",
                'published_at' => null,
                'featured' => false,
            ],
        ];

        foreach ($posts as $post) {
            Blog::query()->updateOrCreate(['slug' => $post['slug']], $post);
        }

        // Shop, blog, and HR showcase data. Counts are intentionally varied
        // and the guards keep `db:seed` safe to run more than once locally.
        if (Product::query()->count() === 0) {
            Product::factory(28)->create();
        }

        if (Brand::query()->count() === 0) {
            Brand::factory(18)->sequence(fn ($sequence): array => ['sort' => $sequence->index + 1])->create();
        }

        if (ProductCategory::query()->count() === 0) {
            ProductCategory::factory(14)->create();
        }

        if (Customer::query()->count() === 0) {
            Customer::factory(90)->create();
        }

        if (Order::query()->count() === 0) {
            $customers = Customer::query()->pluck('id');
            Order::factory(140)->state(fn (): array => [
                'customer_id' => $customers->random(),
            ])->create();
        }

        if (Author::query()->count() === 0) {
            Author::factory(14)->create();
        }

        if (Category::query()->count() === 0) {
            Category::factory(12)->create();
        }

        if (Employee::query()->count() === 0) {
            Employee::factory(85)->create();
        }

        if (Department::query()->count() === 0) {
            Department::factory(10)->create();
        }

        if (Project::query()->count() === 0) {
            Project::factory(18)->create();
        }

        if (LeaveRequest::query()->count() === 0) {
            LeaveRequest::factory(65)->create();
        }

        if (Expense::query()->count() === 0) {
            Expense::factory(100)->create();
        }

        if (Task::query()->count() === 0) {
            Task::factory(120)->create();
        }

        if (Timesheet::query()->count() === 0) {
            Timesheet::factory(180)->create();
        }

        $folder = MediaFolder::query()->updateOrCreate([
            'parent_id' => null,
            'name' => 'Demo assets',
        ]);
        $disk = (string) config('media.disk', 'local');
        $directory = trim((string) config('media.directory', 'media'), '/');
        $assets = [
            ['file_name' => 'welcome-to-inlay.txt', 'mime_type' => 'text/plain', 'extension' => 'txt', 'contents' => "Welcome to the Inlay media library.\n"],
            ['file_name' => 'orders-overview.svg', 'mime_type' => 'image/svg+xml', 'extension' => 'svg', 'contents' => $this->demoAssetSvg('#047857', 'Orders')],
            ['file_name' => 'people-workload.svg', 'mime_type' => 'image/svg+xml', 'extension' => 'svg', 'contents' => $this->demoAssetSvg('#4f46e5', 'People')],
        ];

        foreach ($assets as $asset) {
            $path = $directory.'/'.$asset['file_name'];
            Storage::disk($disk)->put($path, $asset['contents']);
            MediaAsset::query()->updateOrCreate([
                'disk' => $disk,
                'path' => $path,
            ], [
                'folder_id' => $folder->getKey(),
                'file_name' => $asset['file_name'],
                'mime_type' => $asset['mime_type'],
                'extension' => $asset['extension'],
                'size' => strlen($asset['contents']),
                'visibility' => 'private',
                'metadata' => ['description' => 'Seeded demo media asset'],
            ]);
        }
    }

    private function demoAssetSvg(string $color, string $label): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="960" height="540" viewBox="0 0 960 540"><rect width="960" height="540" fill="#f4f4f5"/><rect x="48" y="48" width="864" height="444" rx="28" fill="'.$color.'"/><circle cx="160" cy="170" r="48" fill="#fff" fill-opacity=".2"/><path d="M120 350h720" stroke="#fff" stroke-opacity=".3" stroke-width="2"/><text x="120" y="300" fill="#fff" font-family="Arial,sans-serif" font-size="56" font-weight="700">'.$label.'</text><text x="120" y="380" fill="#fff" fill-opacity=".82" font-family="Arial,sans-serif" font-size="24">PHP-authored Inlay demo asset</text></svg>';
    }
}
