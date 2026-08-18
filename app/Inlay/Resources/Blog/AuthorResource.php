<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Models\Blog\Author;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\IconColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\BooleanFilter;
use Inlay\Tables\Table;

final class AuthorResource extends Resource
{
    protected static string $model = Author::class;

    protected static ?string $label = 'Author';

    protected static ?string $pluralLabel = 'Authors';

    protected static ?string $navigationIcon = 'pen-line';

    protected static ?string $navigationGroup = 'Blog';

    protected static int $navigationSort = 10;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'email', 'bio'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search authors…')->filters([
            BooleanFilter::make('active')->label('Active authors'),
        ])->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->copyable(),
            IconColumn::make('active')->boolean()->trueIcon('check')->falseIcon('x'),
            TextColumn::make('created_at')->label('Joined')->date('M j, Y')->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/authors/{id}')->method('get'),
            Action::make('edit')->url('/admin/authors/{id}/edit')->method('get'),
            Action::make('delete')->color('danger')->url('/admin/authors/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save author')->schema([
            Section::make('author')->label('Author profile')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->autofocus(),
                    TextInput::make('email')->email()->required(),
                    Toggle::make('active')->label('Accepting new posts'),
                ]),
                Textarea::make('bio')->rows(6)->maxLength(2000),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Author'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('active')->badge()->color('success'),
            TextEntry::make('bio')->columnSpanFull()->prose(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => AuthorList::route('/'), 'create' => AuthorCreate::route('/create'), 'view' => AuthorView::route('/{record}'), 'edit' => AuthorEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
