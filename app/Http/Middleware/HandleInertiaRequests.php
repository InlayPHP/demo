<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Inlay\Notifications\NotificationManager;
use Inlay\PanelRegistry;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'inlayPanel' => function () use ($request): mixed {
                $panel = $request->route('inlayPanel');

                return is_string($panel)
                    ? app(PanelRegistry::class)->get($panel)
                    : null;
            },
            'inlayNotifications' => function () use ($request): ?array {
                $user = $request->user();
                if ($user === null) {
                    return null;
                }

                $manager = app(NotificationManager::class);
                $toasts = $manager->pull();
                $all = $manager->databaseNotifications($user, unreadOnly: false, limit: 50);

                return [
                    'all' => $all,
                    'toasts' => $toasts,
                    'unread' => array_values(array_filter(
                        $all,
                        static fn (array $record): bool => $record['read_at'] === null,
                    )),
                ];
            },
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
            ],
            'demoCredentials' => $request->routeIs('home', 'inlay.admin.login')
                ? config('demo.user')
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
