<?php

namespace App\Filament\Resources\ProcessingHistoryResource\Pages;

use App\Filament\Resources\ProcessingHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessingHistories extends ListRecords
{
    protected static string $resource = ProcessingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 