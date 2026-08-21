<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\RelationManagers\Hr\EmployeesRelationManager;
use App\Models\Hr\Department;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\ColorPicker;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Component;
use Inlay\Schemas\Components\Grid;
use Inlay\Support\Condition;
use Inlay\Tables\Columns\ColorColumn;
use Inlay\Tables\Columns\IconColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

/**
 * Manage-style resource: departments are created and edited in modals on the
 * list page (no create/edit/view pages), mirroring Filament's ManageRecords.
 */
final class DepartmentResource extends Resource
{
    protected static string $model = Department::class;

    protected static ?string $label = 'Department';

    protected static ?string $pluralLabel = 'Departments';

    protected static ?string $navigationIcon = 'building-2';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 1;

    public static function recordTitleAttribute(): ?string
    {
        return 'name';
    }

    /** @return list<Component> */
    public static function departmentSchema(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, mixed $state, $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    // NOTE: modal action forms have no model binding, so the
                    // model-aware unique() rule cannot run here; the DB unique
                    // index and the centralized ShowcaseRules enforce it.
                    ->rules('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                Select::make('parent_id')
                    ->label('Parent department')
                    ->options(fn (): array => Department::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                TextInput::make('budget')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(9999999999.99)
                    ->default(0),
                TextInput::make('headcount_limit')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(2147483647)
                    ->default(0),
                ColorPicker::make('color'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->columnStart(1),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('parent.name')->placeholder('Top Level'),
                TextColumn::make('employees_count')->counts('employees')->label('Headcount'),
                TextColumn::make('budget')->money('USD')->sortable(),
                ColorColumn::make('color')->toggleable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    // Manage-mode convention: the index component intercepts the
                    // "edit" action name and opens the edit modal (the URL is a
                    // marker that is never visited on manage-style pages).
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/departments/{id}/edit')
                        ->method('get'),
                    Action::make('adjust_budget')
                        ->label('Adjust budget')
                        ->icon('banknotes')
                        ->color('success')
                        ->modalWidth('xs')
                        ->modalSubmitAction(Action::make('save')->label('Save')->color('primary'))
                        ->fillForm(fn (Department $record): array => ['budget' => $record->budget])
                        ->form([
                            TextInput::make('budget')->numeric()->prefix('$')->minValue(0)->maxValue(9999999999.99)->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Department $record, array $data) => $record->update($data)),
                    Action::make('deactivate')
                        ->label('Deactivate')
                        ->icon('x-mark')
                        ->color('danger')
                        ->visibleWhen(Condition::truthy('is_active'))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Department $record) => $record->update(['is_active' => false])),
                    Action::make('activate')
                        ->label('Activate')
                        ->icon('check')
                        ->color('success')
                        ->visibleWhen(Condition::falsy('is_active'))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Department $record) => $record->update(['is_active' => true])),
                    Action::make('replicate')
                        ->label('Replicate')
                        ->icon('copy')
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Department $record): void {
                            $replica = $record->replicate();
                            $replica->slug = Str::slug((string) $replica->name).'-'.Str::random(5);
                            $replica->save();
                        }),
                    HrActions::cheekyDelete(),
                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                HrActions::cheekyDeleteBulk(),
            ])
            ->recordClasses(fn (Department $record): ?string => match (true) {
                (float) $record->employees_count > (int) $record->headcount_limit && (int) $record->headcount_limit > 0 => 'bg-danger-50 dark:bg-danger-950/50',
                default => null,
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::departmentSchema());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist;
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    /**
     * The disabled slug field's value is stripped from create payloads (Inlay
     * protects disabled fields), so the slug is regenerated server-side.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mutateDataBeforeCreate(array $data): array
    {
        $data['slug'] ??= Str::slug((string) ($data['name'] ?? ''));

        return $data;
    }

    public static function getRelations(): array
    {
        return [EmployeesRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => DepartmentList::route('/')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
