<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\LeaveRequest;
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

final class LeaveRequestResource extends Resource
{
    protected static string $model = LeaveRequest::class;

    protected static ?string $label = 'Leave request';

    protected static ?string $pluralLabel = 'Leave requests';

    protected static ?string $navigationIcon = 'calendar-days';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 30;

    public static function globallySearchableAttributes(): array
    {
        return ['employee', 'type', 'status'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'employee';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search leave requests…')->columns([
            TextColumn::make('employee')->searchable()->sortable(), TextColumn::make('type')->sortable(), BadgeColumn::make('status')->colors(['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']), TextColumn::make('start_date')->date('M j, Y')->sortable(), TextColumn::make('end_date')->date('M j, Y')->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/leave-requests/{id}')->method('get'), Action::make('edit')->url('/admin/leave-requests/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/leave-requests/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save leave request')->schema([
            Section::make('leave')->label('Leave request')->schema([
                Grid::make(2)->schema([
                    TextInput::make('employee')->required()->autofocus(), Select::make('type')->options(['annual' => 'Annual leave', 'sick' => 'Sick leave', 'parental' => 'Parental leave', 'unpaid' => 'Unpaid leave'])->required(), Select::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])->required(), DatePicker::make('start_date')->required(), DatePicker::make('end_date')->required(),
                ]), Textarea::make('notes')->rows(4),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('employee')->label('Employee'), TextEntry::make('type'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('start_date')->date('M j, Y'), TextEntry::make('end_date')->date('M j, Y'), TextEntry::make('notes')->columnSpanFull()->wrap(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => LeaveRequestList::route('/'), 'create' => LeaveRequestCreate::route('/create'), 'view' => LeaveRequestView::route('/{record}'), 'edit' => LeaveRequestEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
