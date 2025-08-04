<?php

namespace App\Filament\Resources\FormattingImprovedTextResource\Pages;

use App\Filament\Resources\FormattingImprovedTextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormattingImprovedText extends EditRecord
{
    protected static string $resource = FormattingImprovedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 