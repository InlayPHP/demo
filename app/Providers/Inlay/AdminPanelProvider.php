<?php

declare(strict_types=1);

namespace App\Providers\Inlay;

use App\Inlay\Resources\Blog\AuthorResource;
use App\Inlay\Resources\Blog\CategoryResource;
use App\Inlay\Resources\BlogResource;
use App\Inlay\Resources\Hr\DepartmentResource;
use App\Inlay\Resources\Hr\EmployeeResource;
use App\Inlay\Resources\Hr\ExpenseResource;
use App\Inlay\Resources\Hr\LeaveRequestResource;
use App\Inlay\Resources\Hr\ProjectResource;
use App\Inlay\Resources\Hr\TaskResource;
use App\Inlay\Resources\Hr\TimesheetResource;
use App\Inlay\Resources\Shop\BrandResource;
use App\Inlay\Resources\Shop\CustomerResource;
use App\Inlay\Resources\Shop\OrderResource;
use App\Inlay\Resources\Shop\ProductCategoryResource;
use App\Inlay\Resources\Shop\ProductResource;
use App\Inlay\Resources\UserResource;
use App\Inlay\Widgets\AdminDashboardWidgets;
use Inlay\MediaManager\MediaManagerPlugin;
use Inlay\NavigationGroup;
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
            ->globalSearch()
            ->middleware(['web'])
            ->authMiddleware(['auth'])
            ->loginComponent('inlay/auth/login')
            ->dashboardComponent('inlay/dashboard')
            ->accountSettings()
            ->resources([
                UserResource::class,
                BlogResource::class,
                ProductResource::class,
                CustomerResource::class,
                OrderResource::class,
                BrandResource::class,
                ProductCategoryResource::class,
                AuthorResource::class,
                CategoryResource::class,
                DepartmentResource::class,
                EmployeeResource::class,
                ProjectResource::class,
                LeaveRequestResource::class,
                ExpenseResource::class,
                TaskResource::class,
                TimesheetResource::class,
            ])
            ->widget(AdminDashboardWidgets::class)
            ->navigationGroups([
                NavigationGroup::make('Shop')->label('Shop')->icon('shopping-bag')->sort(10),
                NavigationGroup::make('Blog')->label('Blog')->icon('newspaper')->sort(20),
                NavigationGroup::make('HR')->label('HR & projects')->icon('briefcase-business')->sort(30),
                NavigationGroup::make('Administration')->label('Administration')->icon('settings')->sort(40),
                NavigationGroup::make('examples')
                    ->label('Package demos')
                    ->icon('braces')
                    ->sort(80),
            ])
            ->navigationItems([
                NavigationItem::make('forms-demo')
                    ->label('Forms demo')
                    ->url('/demo/forms', true)
                    ->icon('braces')
                    ->group('examples')
                    ->sort(10),
                NavigationItem::make('tables-demo')
                    ->label('Tables demo')
                    ->url('/demo/tables', true)
                    ->icon('table')
                    ->group('examples')
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
                    ->group('examples')
                    ->sort(30),
            ])
            ->plugin(MediaManagerPlugin::make());
    }

    protected function isDefaultPanel(): bool
    {
        return true;
    }
}
