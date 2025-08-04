<?php

namespace App\Filament\Resources\FormattingImprovedTextResource\Pages;

use App\Filament\Resources\FormattingImprovedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormattingImprovedTexts extends ListRecords
{
    protected static string $resource = FormattingImprovedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 