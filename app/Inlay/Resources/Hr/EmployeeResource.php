<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Employee;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\CheckboxList;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\KeyValue;
use Inlay\Forms\Fields\Select;
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
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Table;

final class EmployeeResource extends Resource
{
    protected static string $model = Employee::class;

    protected static ?string $label = 'Employee';

    protected static ?string $pluralLabel = 'Employees';

    protected static ?string $navigationIcon = 'briefcase-business';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 10;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'email', 'department'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search employees…')->filters([
            SelectFilter::make('department')->options(['Engineering' => 'Engineering', 'Design' => 'Design', 'Content' => 'Content', 'Operations' => 'Operations', 'Sales' => 'Sales']),
            SelectFilter::make('status')->options(['active' => 'Active', 'on-leave' => 'On leave', 'inactive' => 'Inactive']),
        ])->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('email')->searchable()->copyable(), TextColumn::make('department')->sortable(), BadgeColumn::make('status')->colors(['active' => 'success', 'on-leave' => 'warning', 'inactive' => 'gray']), TextColumn::make('hire_date')->date('M j, Y')->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/employees/{id}')->method('get'), Action::make('edit')->url('/admin/employees/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/employees/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save employee')->schema([
            Section::make('personal')->label('Personal and employment details')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->autofocus(), TextInput::make('email')->email()->required(), TextInput::make('department')->required(), Select::make('employment_type')->options(['full-time' => 'Full-time', 'part-time' => 'Part-time', 'contract' => 'Contract'])->required(), Select::make('status')->options(['active' => 'Active', 'on-leave' => 'On leave', 'inactive' => 'Inactive'])->required(), DatePicker::make('hire_date')->required(), TextInput::make('salary')->numeric()->prefix('$'),
                ]),
                CheckboxList::make('skills')->label('Skills')->options(['Laravel' => 'Laravel', 'React' => 'React', 'Design' => 'Design', 'SQL' => 'SQL', 'Writing' => 'Writing', 'Leadership' => 'Leadership']),
                KeyValue::make('metadata')->label('Metadata')->keyLabel('Property')->valueLabel('Value')->addActionLabel('Add metadata'),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Employee'), TextEntry::make('email')->copyable(), TextEntry::make('department'), TextEntry::make('employment_type')->label('Employment type'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('hire_date')->date('M j, Y'), TextEntry::make('salary')->money('USD'), TextEntry::make('skills')->list()->bulleted()->columnSpanFull(), TextEntry::make('metadata')->label('Metadata')->columnSpanFull(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => EmployeeList::route('/'), 'create' => EmployeeCreate::route('/create'), 'view' => EmployeeView::route('/{record}'), 'edit' => EmployeeEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
