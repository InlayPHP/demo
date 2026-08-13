<?php

declare(strict_types=1);

namespace App\Inlay\Widgets;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;
use Inlay\Media\Models\MediaAsset;
use Inlay\Tables\Columns\BadgeColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;
use Inlay\Widgets\ChartWidget;
use Inlay\Widgets\Contracts\ProvidesWidgets;
use Inlay\Widgets\Stat;
use Inlay\Widgets\StatsOverviewWidget;
use Inlay\Widgets\TableWidget;

final class AdminDashboardWidgets implements ProvidesWidgets
{
    public function widgets(Request $request): iterable
    {
        $dates = collect(range(6, 0))->map(fn (int $days) => now()->subDays($days));

        yield StatsOverviewWidget::make('overview')
            ->label('At a glance')
            ->columns(3)
            ->stats([
                Stat::make('Users', User::query()->count())
                    ->description('Accounts with panel access')
                    ->color('primary')
                    ->url('/admin/users')
                    ->chart([8, 12, 14, 18, 21, 24, User::query()->count()]),
                Stat::make('Blog posts', Blog::query()->count())
                    ->description(Blog::query()->where('status', 'published')->count().' published')
                    ->color('success')
                    ->url('/admin/blogs')
                    ->trend('up'),
                Stat::make('Media assets', MediaAsset::query()->count())
                    ->description('Files in the shared library')
                    ->color('info')
                    ->url('/admin/media'),
            ]);

        yield ChartWidget::make('content-activity')
            ->label('Content activity')
            ->description('New users and published posts during the last seven days')
            ->chartType('bar')
            ->labels(array_values($dates->map(fn ($date) => $date->format('D'))->all()))
            ->dataset('New users', array_values($dates->map(fn ($date) => User::query()->whereDate('created_at', $date)->count())->all()))
            ->dataset('Published posts', array_values($dates->map(fn ($date) => Blog::query()->where('status', 'published')->whereDate('published_at', $date)->count())->all()), '#059669')
            ->columnSpan(7)
            ->sort(10);

        yield TableWidget::make('recent-posts')
            ->label('Recent blog posts')
            ->description('The latest content in this demo resource')
            ->table(Table::make('dashboard_blogs')
                ->columns([
                    TextColumn::make('title')->label('Title')->limit(42),
                    BadgeColumn::make('status')
                        ->label('Status')
                        ->colors(['draft' => 'gray', 'published' => 'success'])
                        ->labels(['draft' => 'Draft', 'published' => 'Published']),
                ])
                ->rows(Blog::query()->latest()->limit(5)->get(['id', 'title', 'status'])))
            ->columnSpan(5)
            ->sort(20);
    }
}
