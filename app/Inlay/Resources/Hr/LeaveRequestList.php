<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericListPage;
use App\Models\Hr\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;
use Inlay\Resources\Pages\PageTab;

final class LeaveRequestList extends GenericListPage
{
    protected static string $resource = LeaveRequestResource::class;

    protected int $perPage = 10;

    protected function tabs(): array
    {
        return [
            PageTab::make('all')->label('All')->badge(fn (): int => LeaveRequest::query()->count())->default(),
            PageTab::make('pending')->label('Needs review')->badge(fn (): int => LeaveRequest::query()->where('status', 'pending')->count())->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'pending')),
            PageTab::make('approved')->label('Approved')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'approved')),
        ];
    }
}
