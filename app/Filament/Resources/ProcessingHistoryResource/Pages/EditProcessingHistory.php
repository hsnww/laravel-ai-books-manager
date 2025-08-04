<?php

namespace App\Filament\Resources\ProcessingHistoryResource\Pages;

use App\Filament\Resources\ProcessingHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessingHistory extends EditRecord
{
    protected static string $resource = ProcessingHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 