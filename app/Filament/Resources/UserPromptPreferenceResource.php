<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPromptPreferenceResource\Pages;
use App\Models\UserPromptPreference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserPromptPreferenceResource extends Resource
{
    protected static ?string $model = UserPromptPreference::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
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
        return __('Prompt Preferences');
    }

    public static function getModelLabel(): string
    {
        return __('Prompt Preference');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Prompt Preferences');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('preferred_language')
                    ->label('اللغة المفضلة')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
                    ])
                    ->required()
                    ->default('arabic'),

                Forms\Components\Select::make('default_prompt_id')
                    ->label('التوجيه الافتراضي')
                    ->relationship('defaultPrompt', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('preferred_language')
                    ->label('اللغة المفضلة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'arabic' => 'success',
                        'english' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('defaultPrompt.name')
                    ->label('التوجيه الافتراضي')
                    ->searchable(),

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
                Tables\Filters\SelectFilter::make('preferred_language')
                    ->label('اللغة المفضلة')
                    ->options([
                        'arabic' => 'العربية',
                        'english' => 'English',
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
            'index' => Pages\ListUserPromptPreferences::route('/'),
            'create' => Pages\CreateUserPromptPreference::route('/create'),
            'edit' => Pages\EditUserPromptPreference::route('/{record}/edit'),
        ];
    }
} 