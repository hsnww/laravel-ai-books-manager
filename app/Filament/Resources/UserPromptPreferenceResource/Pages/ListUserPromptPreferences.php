<?php

namespace App\Filament\Resources\UserPromptPreferenceResource\Pages;

use App\Filament\Resources\UserPromptPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserPromptPreferences extends ListRecords
{
    protected static string $resource = UserPromptPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 