<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Widgets\Hr\TimesheetStats;
use App\Models\Hr\Employee;
use App\Models\Hr\Project;
use App\Models\Hr\Task;
use App\Models\Hr\Timesheet;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Actions\BulkAction;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\IconColumn;
use Inlay\Tables\Columns\Summarizers\Average;
use Inlay\Tables\Columns\Summarizers\Count;
use Inlay\Tables\Columns\Summarizers\Range;
use Inlay\Tables\Columns\Summarizers\Sum;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\DateFilter;
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Filters\TernaryFilter;
use Inlay\Tables\Grouping\Group;
use Inlay\Tables\Table;

final class TimesheetResource extends Resource
{
    protected static string $model = Timesheet::class;

    protected static ?string $label = 'Timesheet';

    protected static ?string $pluralLabel = 'Timesheets';

    protected static ?string $navigationIcon = 'clock-3';

    protected static ?string $navigationGroup = 'Projects';

    protected static int $navigationSort = 6;

    public static function widgets(): array
    {
        return [TimesheetStats::class];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('employee.name')->searchable()->sortable()->weight('medium')->summarize(Count::make()->all()),
                TextColumn::make('project.name')->searchable()->sortable(),
                TextColumn::make('task.title')->searchable()->limit(30)->toggleable()->placeholder('No task'),
                TextColumn::make('date')->date()->sortable()->summarize(Range::make()),
                TextColumn::make('hours')
                    ->numeric(1)
                    ->sortable()
                    ->summarize([Sum::make()->label('Total'), Average::make()->label('Avg')]),
                IconColumn::make('is_billable')->label('Billable')->boolean()->toggleable(),
                TextColumn::make('hourly_rate')->money('USD')->sortable()->toggleable()->copyable(),
                TextColumn::make('total_cost')->money('USD')->sortable()->summarize(Sum::make()->money('USD')),
            ])
            ->groups([
                Group::make('employee.name')->label('Employee')->collapsible(),
                Group::make('project.name')->label('Project')->collapsible(),
                Group::make('date')->label('Date')->date()->collapsible(),
            ])
            ->filters([
                SelectFilter::make('employee')->relationship('employee', 'name'),
                SelectFilter::make('project')->relationship('project', 'name'),
                TernaryFilter::make('is_billable'),
                DateFilter::make('from')
                    ->label('From')
                    ->query(fn (Builder $query, mixed $value): Builder => $value === null || $value === ''
                        ? $query
                        : $query->whereDate('date', '>=', $value))
                    ->indicateUsing(fn (mixed $value): ?string => $value === null || $value === ''
                        ? null
                        : 'From '.Carbon::parse($value)->toFormattedDateString()),
                DateFilter::make('until')
                    ->label('Until')
                    ->query(fn (Builder $query, mixed $value): Builder => $value === null || $value === ''
                        ? $query
                        : $query->whereDate('date', '<=', $value))
                    ->indicateUsing(fn (mixed $value): ?string => $value === null || $value === ''
                        ? null
                        : 'Until '.Carbon::parse($value)->toFormattedDateString()),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('toggle_billable')
                        ->label('Toggle billable')
                        ->icon('currency-dollar')
                        ->iconButton()
                        ->color('success')
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Timesheet $record) => $record->update(['is_billable' => ! $record->is_billable])),
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/timesheets/{id}/edit')
                        ->method('get'),                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                BulkAction::make('mark_billable')
                    ->label('Mark billable')
                    ->icon('currency-dollar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorizeUsing(HrActions::authorized())
                    ->action(fn (Collection $records) => $records->each(
                        fn (Timesheet $record) => $record->update(['is_billable' => true]),
                    ))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('mark_non_billable')
                    ->label('Mark non-billable')
                    ->icon('no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->authorizeUsing(HrActions::authorized())
                    ->action(fn (Collection $records) => $records->each(
                        fn (Timesheet $record) => $record->update(['is_billable' => false]),
                    ))
                    ->deselectRecordsAfterCompletion(),
                HrActions::cheekyDeleteBulk(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save timesheet')->schema([
            Section::make('timesheet_entry')->label('Timesheet Entry')->columns(2)->columnSpanFull()->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, $set): void {
                        if (! $state) {
                            return;
                        }
                        $employee = Employee::query()->find($state);
                        if ($employee instanceof Employee && $employee->hourly_rate) {
                            $set('hourly_rate', $employee->hourly_rate);
                        }
                    }),
                Select::make('project_id')
                    ->label('Project')
                    ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->live(),
                Select::make('task_id')
                    ->label('Task')
                    ->options(fn ($get): array => Task::query()
                        ->when($get('project_id'), fn (Builder $query, mixed $projectId): Builder => $query->where('project_id', $projectId))
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
                DatePicker::make('date')->required()->default(now()),
                TextInput::make('hours')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0)
                    ->maxValue(999.9)
                    ->suffix('hours')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => self::calculateTotalCost($get, $set)),
                TextInput::make('minutes')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(59)
                    ->mask('99')
                    ->suffix('min')
                    ->default(0),
                Toggle::make('is_billable')->label('Billable')->default(true)->columnSpanFull(),
                TextInput::make('hourly_rate')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(999999.99)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => self::calculateTotalCost($get, $set)),
                TextInput::make('total_cost')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->disabled()
                    ->dehydrated(),
                Textarea::make('description')->rows(2)->maxLength(65535)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist;
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    /** @param array<string, mixed> $data */
    protected static function mutateDataBeforeCreate(array $data): array
    {
        $data['total_cost'] = (float) ($data['hours'] ?? 0) * (float) ($data['hourly_rate'] ?? 0);

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected static function mutateDataBeforeUpdate(array $data, Model $record): array
    {
        $data['total_cost'] = (float) ($data['hours'] ?? 0) * (float) ($data['hourly_rate'] ?? 0);

        return $data;
    }

    private static function calculateTotalCost($get, $set): void
    {
        $hours = (float) ($get('hours') ?? 0);
        $rate = (float) ($get('hourly_rate') ?? 0);
        $set('total_cost', number_format($hours * $rate, 2, '.', ''));
    }

    public static function getPages(): array
    {
        return [
            'index' => TimesheetList::route('/'),
            'create' => TimesheetCreate::route('/create'),
            'edit' => TimesheetEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
