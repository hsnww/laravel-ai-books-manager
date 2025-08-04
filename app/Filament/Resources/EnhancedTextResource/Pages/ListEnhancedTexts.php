<?php

namespace App\Filament\Resources\EnhancedTextResource\Pages;

use App\Filament\Resources\EnhancedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnhancedTexts extends ListRecords
{
    protected static string $resource = EnhancedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
