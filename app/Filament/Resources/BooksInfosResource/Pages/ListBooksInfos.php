<?php

namespace App\Filament\Resources\BooksInfosResource\Pages;

use App\Filament\Resources\BooksInfosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBooksInfos extends ListRecords
{
    protected static string $resource = BooksInfosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
