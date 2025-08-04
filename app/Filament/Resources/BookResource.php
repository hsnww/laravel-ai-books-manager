<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'إدارة البيانات';
    protected static ?string $navigationLabel = 'الكتب';
    protected static ?string $modelLabel = 'كتاب';
    protected static ?string $pluralModelLabel = 'الكتب';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('book_identify')
                    ->label('معرف الكتاب')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                
                Forms\Components\Select::make('book_language')
                    ->label('لغة الكتاب')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
                        'french' => 'Français',
                        'german' => 'Deutsch',
                        'spanish' => 'Español',
                        'italian' => 'Italiano',
                        'portuguese' => 'Português',
                        'russian' => 'Русский',
                        'chinese' => '中文',
                        'japanese' => '日本語',
                        'korean' => '한국어',
                        'turkish' => 'Türkçe',
                        'hindi' => 'हिन्दी',
                        'urdu' => 'اردو',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book_identify')
                    ->label('معرف الكتاب')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('book_language')
                    ->label('لغة الكتاب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'arabic' => 'success',
                        'english' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('book_language')
                    ->label('لغة الكتاب')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
                        'french' => 'Français',
                        'german' => 'Deutsch',
                        'spanish' => 'Español',
                        'italian' => 'Italiano',
                        'portuguese' => 'Português',
                        'russian' => 'Русский',
                        'chinese' => '中文',
                        'japanese' => '日本語',
                        'korean' => '한국어',
                        'turkish' => 'Türkçe',
                        'hindi' => 'हिन्दी',
                        'urdu' => 'اردو',
                    ]),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
} 