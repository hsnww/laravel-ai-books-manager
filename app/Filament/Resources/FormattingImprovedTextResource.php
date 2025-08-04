<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormattingImprovedTextResource\Pages;
use App\Models\FormattingImprovedText;
use App\Traits\DeletesProcessedTextFiles;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FormattingImprovedTextResource extends Resource
{
    use DeletesProcessedTextFiles;
    protected static ?string $model = FormattingImprovedText::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'إدارة النصوص المعالجة';
    protected static ?string $navigationLabel = 'النصوص الملخصة نقاط';

    protected static ?string $modelLabel = 'نص ملخص نقاط';

    protected static ?string $pluralModelLabel = 'النصوص الملخصة نقاط';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('book_id')
                    ->label('معرف الكتاب')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('original_file')
                    ->label('الملف الأصلي')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('title')
                    ->label('عنوان النص المنسق')
                    ->required()
                    ->maxLength(500),

                Forms\Components\Textarea::make('improved_text')
                    ->label('النص المحسن')
                    ->required()
                    ->rows(20)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->required()
                    ->maxLength(50),

                Forms\Components\DateTimePicker::make('processing_date')
                    ->label('تاريخ المعالجة')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('book_id')
                    ->label('معرف الكتاب')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('original_file')
                    ->label('الملف الأصلي')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان النص المنسق')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('processing_date')
                    ->label('تاريخ المعالجة')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->options([
                        'Arabic' => 'العربية',
                        'English' => 'الإنجليزية',
                        'French' => 'الفرنسية',
                        'Spanish' => 'الإسبانية',
                        'German' => 'الألمانية',
                        'Italian' => 'الإيطالية',
                        'Portuguese' => 'البرتغالية',
                        'Russian' => 'الروسية',
                        'Chinese' => 'الصينية',
                        'Japanese' => 'اليابانية',
                        'Korean' => 'الكورية',
                        'Turkish' => 'التركية',
                        'Persian' => 'الفارسية',
                        'Urdu' => 'الأردية',
                        'Hindi' => 'الهندية',
                        'Bengali' => 'البنغالية',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (\App\Models\FormattingImprovedText $record) {
                        // حذف الملف النصي قبل حذف السجل
                        self::deleteProcessedTextFile($record, 'improve_format');
                    }),
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
            'index' => Pages\ListFormattingImprovedTexts::route('/'),
            'create' => Pages\CreateFormattingImprovedText::route('/create'),
            'edit' => Pages\EditFormattingImprovedText::route('/{record}/edit'),
        ];
    }
    

} 