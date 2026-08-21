<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Timesheet;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\BadgeColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class TimesheetResource extends Resource
{
    protected static string $model = Timesheet::class;

    protected static ?string $label = 'Timesheet';

    protected static ?string $pluralLabel = 'Timesheets';

    protected static ?string $navigationIcon = 'clock-3';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 60;

    public static function globallySearchableAttributes(): array
    {
        return ['employee', 'project', 'status'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'employee';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search timesheets…')->columns([
            TextColumn::make('employee')->searchable()->sortable(), TextColumn::make('project')->searchable(), TextColumn::make('work_date')->date('M j, Y')->sortable(), TextColumn::make('hours')->numeric()->suffix(' h')->sortable()->alignment('right'), BadgeColumn::make('status')->colors(['draft' => 'gray', 'submitted' => 'warning', 'approved' => 'success']),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/timesheets/{id}')->method('get'), Action::make('edit')->url('/admin/timesheets/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/timesheets/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save timesheet')->schema([Section::make('timesheet')->label('Timesheet entry')->schema([Grid::make(2)->schema([
            TextInput::make('employee')->required()->autofocus(), TextInput::make('project'), DatePicker::make('work_date')->required(), TextInput::make('hours')->numeric()->suffix(' hours')->required(), Select::make('status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved'])->required(),
        ]), Textarea::make('notes')->rows(4)])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([TextEntry::make('employee')->label('Employee'), TextEntry::make('project'), TextEntry::make('work_date')->date('M j, Y'), TextEntry::make('hours')->suffix(' hours'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('notes')->columnSpanFull()->wrap()]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => TimesheetList::route('/'), 'create' => TimesheetCreate::route('/create'), 'view' => TimesheetView::route('/{record}'), 'edit' => TimesheetEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
