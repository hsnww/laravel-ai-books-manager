<?php

namespace App\Filament\Resources\UploadManagerResource\Pages;

use App\Filament\Resources\UploadManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUploadManager extends EditRecord
{
    protected static string $resource = UploadManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
