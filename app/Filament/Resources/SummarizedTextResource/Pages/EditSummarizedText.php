<?php

namespace App\Filament\Resources\SummarizedTextResource\Pages;

use App\Filament\Resources\SummarizedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSummarizedText extends EditRecord
{
    protected static string $resource = SummarizedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
