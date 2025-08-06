<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BooksInfosResource\Pages;
use App\Filament\Resources\BooksInfosResource\RelationManagers;
use App\Models\BookInfo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BooksInfosResource extends Resource
{
    protected static ?string $model = BookInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function getNavigationGroup(): string
    {
        return __('Data Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Books Information');
    }

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getModelLabel(): string
    {
        return __('Book Information');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Books Information');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->relationship('book', 'book_identify')
                    ->label('الكتاب')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author')
                    ->label('المؤلف')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('book_summary')
                    ->label('ملخص الكتاب')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('language')
                    ->label('اللغة')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('book.book_identify')
                    ->label('معرف الكتاب')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('المؤلف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('language')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'الإنجليزية',
                        'fr' => 'الفرنسية',
                        'es' => 'الإسبانية',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListBooksInfos::route('/'),
            'create' => Pages\CreateBooksInfos::route('/create'),
            'edit' => Pages\EditBooksInfos::route('/{record}/edit'),
        ];
    }
}
