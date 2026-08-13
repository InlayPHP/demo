<?php

namespace Database\Seeders;

use App\Models\Blog;
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

        $folder = MediaFolder::query()->updateOrCreate([
            'parent_id' => null,
            'name' => 'Demo assets',
        ]);
        $disk = (string) config('media.disk', 'local');
        $path = trim((string) config('media.directory', 'media'), '/').'/welcome-to-inlay.txt';
        $contents = "Welcome to the Inlay media library.\n";

        Storage::disk($disk)->put($path, $contents);
        MediaAsset::query()->updateOrCreate([
            'disk' => $disk,
            'path' => $path,
        ], [
            'folder_id' => $folder->getKey(),
            'file_name' => 'welcome-to-inlay.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => strlen($contents),
            'visibility' => 'private',
            'metadata' => ['description' => 'Seeded demo media asset'],
        ]);
    }
}
