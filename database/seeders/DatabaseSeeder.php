<?php

namespace Database\Seeders;

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
