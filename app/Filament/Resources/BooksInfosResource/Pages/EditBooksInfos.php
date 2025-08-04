<?php

namespace App\Filament\Resources\BooksInfosResource\Pages;

use App\Filament\Resources\BooksInfosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooksInfos extends EditRecord
{
    protected static string $resource = BooksInfosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
