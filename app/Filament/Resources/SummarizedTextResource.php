<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SummarizedTextResource\Pages;
use App\Filament\Resources\SummarizedTextResource\RelationManagers;
use App\Models\SummarizedText;
use App\Traits\DeletesProcessedTextFiles;
use App\Helpers\LanguageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SummarizedTextResource extends Resource
{
    use DeletesProcessedTextFiles;
    protected static ?string $model = SummarizedText::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): string
    {
        return __('Processed Texts Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Summarized Texts');
    }

    public static function getModelLabel(): string
    {
        return __('Summarized Text');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Summarized Texts');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'book_identify')
                    ->required()
                    ->searchable(),
                
                Forms\Components\TextInput::make('original_file')
                    ->label('الملف الأصلي')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الملخص')
                    ->required()
                    ->maxLength(500),
                
                Forms\Components\Textarea::make('summarized_text')
                    ->label('النص الملخص')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->options(LanguageHelper::getLanguageOptionsForForms())
                    ->required(),
                
                Forms\Components\TextInput::make('summary_length')
                    ->label('طول الملخص')
                    ->numeric()
                    ->maxLength(255),
                
                Forms\Components\DateTimePicker::make('processing_date')
                    ->label('تاريخ المعالجة')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book.book_identify')
                    ->label('معرف الكتاب')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('original_file')
                    ->label('الملف الأصلي')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الملخص')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('summarized_text')
                    ->label('النص الملخص')
                    ->limit(100)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'arabic' => 'success',
                        'english' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('summary_length')
                    ->label('طول الملخص')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('processing_date')
                    ->label('تاريخ المعالجة')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->options(LanguageHelper::getLanguageOptionsForForms()),
                
                Tables\Filters\SelectFilter::make('book_id')
                    ->label('الكتاب')
                    ->relationship('book', 'book_identify'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (\App\Models\SummarizedText $record) {
                        // حذف الملف النصي قبل حذف السجل
                        self::deleteProcessedTextFile($record, 'summarize');
                    }),
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
            'index' => Pages\ListSummarizedTexts::route('/'),
            'create' => Pages\CreateSummarizedText::route('/create'),
            'edit' => Pages\EditSummarizedText::route('/{record}/edit'),
        ];
    }
    

}
