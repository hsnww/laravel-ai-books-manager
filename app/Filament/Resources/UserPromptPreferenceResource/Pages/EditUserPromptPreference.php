<?php

namespace App\Filament\Resources\UserPromptPreferenceResource\Pages;

use App\Filament\Resources\UserPromptPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserPromptPreference extends EditRecord
{
    protected static string $resource = UserPromptPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 