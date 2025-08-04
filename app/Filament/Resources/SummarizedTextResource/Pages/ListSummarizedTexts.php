<?php

namespace App\Filament\Resources\SummarizedTextResource\Pages;

use App\Filament\Resources\SummarizedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSummarizedTexts extends ListRecords
{
    protected static string $resource = SummarizedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
