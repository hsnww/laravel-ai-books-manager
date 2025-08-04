<?php

namespace App\Filament\Resources\TranslatedTextResource\Pages;

use App\Filament\Resources\TranslatedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTranslatedTexts extends ListRecords
{
    protected static string $resource = TranslatedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
