<?php

use App\Inlay\Forms\CreateDemoUser;
use App\Inlay\Tables\ListDemoUsers;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome', [
    'demoCredentials' => config('demo.user'),
]))->name('home');

$demoPages = [
    'forms' => [
        'slug' => 'forms',
        'eyebrow' => 'Schema-driven forms',
        'title' => 'Forms that stay clear as they grow.',
        'description' => 'A focused look at reusable fields, validation, and layout composition for Laravel applications.',
        'next' => 'Connect the preview to the Inlay form schema and validation packages.',
    ],
    'tables' => [
        'slug' => 'tables',
        'eyebrow' => 'Data workflows',
        'title' => 'Tables made for real workflows.',
        'description' => 'Search, filter, sort, paginate, and act on data without losing the calm of the surrounding interface.',
        'next' => 'Connect the preview to the Inlay table query and action APIs.',
    ],
];

Route::inlayForm('/demo/forms', CreateDemoUser::class)
    ->name('demo.forms');

Route::inlayTable('/demo/tables', ListDemoUsers::class)
    ->name('demo.tables');

Route::redirect('/demo/full-feature', '/admin')->name('demo.full-feature');
