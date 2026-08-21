<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use App\Models\User;
use App\Validation\UserRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class UserResource extends Resource
{
    protected static string $model = User::class;

    protected static ?string $navigationIcon = 'users';

    protected static ?string $navigationGroup = 'Administration';

    protected static int $navigationSort = 10;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search users…')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('M j, Y')
                    ->placeholder('Not verified')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->url('/admin/users/{id}/edit')
                    ->method('get'),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->url('/admin/users/{id}')
                    ->method('delete')
                    ->requiresConfirmation()
                    ->authorizeUsing(fn (Request $request, User $record): bool => ! $record->is($request->user())),
            ])
            ->paginationPageOptions([10, 25, 50])
            ->emptyState('No users found', 'Create the first account for this panel.');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->submitLabel('Save user')
            ->schema([
                Section::make('account')
                    ->label('Account details')
                    ->description('Manage the identity used to sign in to this Inlay panel.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->autofocus()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            TextInput::make('password')
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText('Required for new users. Leave blank while editing to keep the current password.')
                                ->minLength(8)
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }

    public static function validation(): string
    {
        return UserRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $operation !== ResourceOperation::Delete || ! $record?->is($user);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mutateDataBeforeCreate(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mutateDataBeforeUpdate(array $data, Model $record): array
    {
        if (($data['password'] ?? '') === '') {
            unset($data['password']);
        }

        return $data;
    }
}
