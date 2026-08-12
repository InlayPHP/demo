<?php

namespace App\Inlay\Tables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;
use Inlay\Tables\TablePage;

final class ListDemoUsers extends TablePage
{
    protected static string $component = 'demo/table';

    protected function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search users…')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ]);
    }

    /** @return Builder<User> */
    protected function query(Request $request): Builder
    {
        return User::query()->latest('created_at');
    }
}
