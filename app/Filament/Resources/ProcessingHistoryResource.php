<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessingHistoryResource\Pages;
use App\Models\ProcessingHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProcessingHistoryResource extends Resource
{
    protected static ?string $model = ProcessingHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): string
    {
        return __('System Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Processing History');
    }

    public static function getModelLabel(): string
    {
        return __('Processing');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Processing History');
    }

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

                Forms\Components\Select::make('processing_type')
                    ->label('نوع المعالجة')
                    ->options([
                        'enhance' => 'تحسين النص',
                        'translate' => 'ترجمة النص',
                        'summarize' => 'تلخيص النص',
                        'improve_language' => 'تحسين اللغة',
                        'improve_format' => 'تلخيص النص على هيئة نقاط',
                        'extract_book_info' => 'استخراج معلومات الكتاب',
                        'enhance_translate' => 'تحسين وترجمة',
                        'enhance_summarize' => 'تحسين وتلخيص',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->required()
                    ->maxLength(50),

                Forms\Components\Select::make('processing_status')
                    ->label('حالة المعالجة')
                    ->options([
                        'success' => 'نجح',
                        'failed' => 'فشل',
                        'in_progress' => 'قيد المعالجة',
                    ])
                    ->required()
                    ->default('success'),

                Forms\Components\Textarea::make('error_message')
                    ->label('رسالة الخطأ')
                    ->rows(3)
                    ->maxLength(65535),

                Forms\Components\TextInput::make('processing_time_seconds')
                    ->label('وقت المعالجة (ثواني)')
                    ->numeric()
                    ->step(0.01),
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

                Tables\Columns\TextColumn::make('processing_type')
                    ->label('نوع المعالجة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'enhance' => 'success',
                        'translate' => 'info',
                        'summarize' => 'warning',
                        'improve_language' => 'primary',
                        'improve_format' => 'secondary',
                        'extract_book_info' => 'danger',
                        'enhance_translate' => 'success',
                        'enhance_summarize' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('target_language')
                    ->label('اللغة المستهدفة')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('processing_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'in_progress' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('processing_time_seconds')
                    ->label('وقت المعالجة')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->suffix(' ثانية'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ المعالجة')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('processing_type')
                    ->label('نوع المعالجة')
                    ->options([
                        'enhance' => 'تحسين النص',
                        'translate' => 'ترجمة النص',
                        'summarize' => 'تلخيص النص',
                        'improve_language' => 'تحسين اللغة',
                        'improve_format' => 'تلخيص النص على هيئة نقاط',
                        'extract_book_info' => 'استخراج معلومات الكتاب',
                        'enhance_translate' => 'تحسين وترجمة',
                        'enhance_summarize' => 'تحسين وتلخيص',
                    ]),

                Tables\Filters\SelectFilter::make('processing_status')
                    ->label('حالة المعالجة')
                    ->options([
                        'success' => 'نجح',
                        'failed' => 'فشل',
                        'in_progress' => 'قيد المعالجة',
                    ]),

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
            'index' => Pages\ListProcessingHistories::route('/'),
            'create' => Pages\CreateProcessingHistory::route('/create'),
            'edit' => Pages\EditProcessingHistory::route('/{record}/edit'),
        ];
    }
} 