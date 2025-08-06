<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiPromptResource\Pages;
use App\Models\AiPrompt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AiPromptResource extends Resource
{
    protected static ?string $model = AiPrompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
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
        return __('AI Prompts');
    }

    public static function getModelLabel(): string
    {
        return __('Prompt');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Prompts');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم التوجيه')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('وصف التوجيه')
                    ->rows(3)
                    ->maxLength(65535),

                Forms\Components\Select::make('language')
                    ->label('لغة التوجيه')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
                    ])
                    ->required()
                    ->default('arabic'),

                Forms\Components\Select::make('prompt_type')
                    ->label('نوع التوجيه')
                    ->options([
                        'extract_info' => 'استخراج معلومات الكتاب',
                        'summarize' => 'تلخيص النص',
                        'translate' => 'ترجمة النص',
                        'enhance' => 'تحسين النص',
                        'improve_format' => 'تلخيص النص على هيئة نقاط',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('prompt_text')
                    ->label('نص التوجيه')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),

                Forms\Components\Toggle::make('is_default')
                    ->label('افتراضي')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم التوجيه')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('prompt_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'extract_info' => 'استخراج معلومات الكتاب',
                        'summarize' => 'تلخيص النص',
                        'translate' => 'ترجمة النص',
                        'enhance' => 'تحسين النص',
                        'improve_format' => 'تلخيص النص على هيئة نقاط',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'extract_info' => 'danger',
                        'summarize' => 'warning',
                        'translate' => 'info',
                        'enhance' => 'success',
                        'improve_format' => 'secondary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('language')
                    ->label('اللغة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'arabic' => 'العربية',
                        'english' => 'English',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'arabic' => 'success',
                        'english' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('افتراضي')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prompt_type')
                    ->label('نوع التوجيه')
                    ->options([
                        'extract_info' => 'استخراج معلومات الكتاب',
                        'summarize' => 'تلخيص النص',
                        'translate' => 'ترجمة النص',
                        'enhance' => 'تحسين النص',
                        'improve_format' => 'تلخيص النص على هيئة نقاط',
                    ]),

                Tables\Filters\SelectFilter::make('language')
                    ->label('اللغة')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('افتراضي'),
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
            'index' => Pages\ListAiPrompts::route('/'),
            'create' => Pages\CreateAiPrompt::route('/create'),
            'edit' => Pages\EditAiPrompt::route('/{record}/edit'),
        ];
    }
}
