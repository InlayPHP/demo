<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use App\Models\Blog;
use App\Validation\BlogRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\DateTimePicker;
use Inlay\Forms\Fields\Select;
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
use Inlay\Tables\Columns\BadgeColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class BlogResource extends Resource
{
    protected static string $model = Blog::class;

    protected static ?string $label = 'Blog post';

    protected static ?string $pluralLabel = 'Blog posts';

    protected static ?string $navigationIcon = 'newspaper';

    protected static ?string $navigationGroup = 'Blog';

    protected static int $navigationSort = 30;

    public static function globallySearchableAttributes(): array
    {
        return ['title', 'slug', 'excerpt'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'title';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search blog posts…')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(64),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                    ])
                    ->labels([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->placeholder('Not published')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->url('/admin/blogs/{id}')
                    ->method('get'),
                Action::make('edit')
                    ->label('Edit')
                    ->url('/admin/blogs/{id}/edit')
                    ->method('get'),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->url('/admin/blogs/{id}')
                    ->method('delete')
                    ->requiresConfirmation(),
            ])
            ->paginationPageOptions([10, 25, 50])
            ->emptyState('No blog posts found', 'Create the first post for this demo.');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->submitLabel('Save blog post')
            ->schema([
                Section::make('post')
                    ->label('Post details')
                    ->description('A small resource showing how Inlay forms and tables work together.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Title')
                                ->required()
                                ->autofocus()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->helperText('Used in URLs and global search.')
                                ->required()
                                ->maxLength(255),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Published',
                                ])
                                ->required(),
                            DateTimePicker::make('published_at')
                                ->label('Published at')
                                ->helperText('Leave blank while the post is still a draft.'),
                            Textarea::make('excerpt')
                                ->label('Excerpt')
                                ->rows(3)
                                ->maxLength(500),
                            Toggle::make('featured')
                                ->label('Featured post')
                                ->helperText('Highlight this post in future dashboard extensions.'),
                        ]),
                        Textarea::make('body')
                            ->label('Body')
                            ->rows(12)
                            ->required()
                            ->maxLength(50000),
                    ]),
            ]);
    }

    public static function validation(): string
    {
        return BlogRules::class;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('title')->label('Title')->columnSpanFull(),
            TextEntry::make('slug')->copyable(),
            TextEntry::make('status')->badge()->color('success'),
            TextEntry::make('published_at')->dateTime('M j, Y H:i')->placeholder('Not published'),
            TextEntry::make('featured')->badge()->color('warning'),
            TextEntry::make('excerpt')->columnSpanFull()->wrap(),
            TextEntry::make('body')->label('Body')->prose()->columnSpanFull(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'view' => ViewBlog::route('/{record}'),
            'edit' => EditBlog::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
