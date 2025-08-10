<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogArticleResource\Pages;
use App\Models\BlogArticle;
use App\Helpers\LanguageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class BlogArticleResource extends Resource
{
    protected static ?string $model = BlogArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function getNavigationGroup(): string
    {
        return __('Content Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Blog Articles');
    }

    public static function getModelLabel(): string
    {
        return __('Blog Article');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Blog Articles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Article Information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('book_id')
                            ->label(__('Book'))
                            ->relationship('book', 'book_identify')
                            ->searchable()
                            ->required(),

                        TextInput::make('original_file')
                            ->label(__('Original File'))
                            ->required()
                            ->maxLength(255),

                        Select::make('target_language')
                            ->label('اللغة المستهدفة')
                            ->options(LanguageHelper::getLanguageOptionsForForms())
                            ->required(),

                        Select::make('article_type')
                            ->label(__('Article Type'))
                            ->options([
                                'blog' => 'Blog Post',
                                'review' => 'Book Review',
                                'summary' => 'Book Summary',
                                'analysis' => 'Book Analysis',
                                'guide' => 'Study Guide',
                            ])
                            ->default('blog')
                            ->required(),

                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('Content'))
                    ->schema([
                        RichEditor::make('article_content')
                            ->label(__('Article Content'))
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'h4',
                                'blockquote',
                                'codeBlock',
                            ]),

                        Textarea::make('seo_keywords')
                            ->label(__('SEO Keywords'))
                            ->placeholder('keyword1, keyword2, keyword3')
                            ->helperText(__('Separate keywords with commas'))
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make(__('Statistics'))
                    ->schema([
                        TextInput::make('word_count')
                            ->label(__('Word Count'))
                            ->numeric()
                            ->disabled(),

                        TextInput::make('processing_date')
                            ->label(__('Processing Date'))
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('book.book_identify')
                    ->label(__('Book'))
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),

                BadgeColumn::make('article_type')
                    ->label(__('Type'))
                    ->colors([
                        'primary' => 'blog',
                        'secondary' => 'review',
                        'info' => 'summary',
                        'warning' => 'analysis',
                        'success' => 'guide',
                    ]),

                TextColumn::make('target_language')
                    ->label(__('Language'))
                    ->sortable(),

                TextColumn::make('word_count')
                    ->label(__('Words'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('processing_date')
                    ->label(__('Processed'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('article_type')
                    ->label(__('Article Type'))
                    ->options([
                        'blog' => 'Blog Post',
                        'review' => 'Book Review',
                        'summary' => 'Book Summary',
                        'analysis' => 'Book Analysis',
                        'guide' => 'Study Guide',
                    ]),

                Tables\Filters\SelectFilter::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->options(LanguageHelper::getLanguageOptionsForForms()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogArticles::route('/'),
            'create' => Pages\CreateBlogArticle::route('/create'),
            'edit' => Pages\EditBlogArticle::route('/{record}/edit'),
        ];
    }
} 