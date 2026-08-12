<?php

declare(strict_types=1);

namespace App\Providers\Inlay;

use App\Inlay\Resources\UserResource;
use Inlay\MediaManager\MediaManagerPlugin;
use Inlay\NavigationItem;
use Inlay\Panel;
use Inlay\PanelProvider;
use Inlay\Theme\Theme;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->path('/admin')
            ->brandName('Inlay Demo')
            ->theme(
                Theme::default()
                    ->named('inlay-demo')
                    ->accent('#047857', '#ffffff')
                    ->darkTokens([
                        'accent' => '#34d399',
                        'accent-foreground' => '#052e16',
                    ])
                    ->font("'Instrument Sans', ui-sans-serif, system-ui, sans-serif"),
            )
            ->sidebarNavigation()
            ->collapsible()
            ->breadcrumbs()
            ->topbar()
            ->spa()
            ->globalSearch(false)
            ->middleware(['web'])
            ->authMiddleware(['auth'])
            ->loginComponent('inlay/auth/login')
            ->dashboardComponent('inlay/dashboard')
            ->accountSettings()
            ->resources([
                UserResource::class,
            ])
            ->navigationItems([
                NavigationItem::make('forms-demo')
                    ->label('Forms demo')
                    ->url('/demo/forms', true)
                    ->icon('braces')
                    ->sort(10),
                NavigationItem::make('tables-demo')
                    ->label('Tables demo')
                    ->url('/demo/tables', true)
                    ->icon('table')
                    ->sort(20),
                NavigationItem::make('account-settings')
                    ->label('My account')
                    ->url('/admin/settings/account')
                    ->icon('user-circle')
                    ->sort(25),
                NavigationItem::make('source')
                    ->label('GitHub')
                    ->url('https://github.com/InlayPHP/inlay', true)
                    ->icon('github')
                    ->sort(30),
            ])
            ->plugin(MediaManagerPlugin::make());
    }

    protected function isDefaultPanel(): bool
    {
        return true;
    }
}
