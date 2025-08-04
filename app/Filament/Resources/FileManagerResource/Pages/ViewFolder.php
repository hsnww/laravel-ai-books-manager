<?php

namespace App\Filament\Resources\FileManagerResource\Pages;

use App\Filament\Resources\FileManagerResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\FileManager;
use Illuminate\Database\Eloquent\Builder;

class ViewFolder extends ListRecords
{
    protected static string $resource = FileManagerResource::class;

    public ?string $folderName = null;

    public function mount(): void
    {
        $this->folderName = request()->route('folder');
    }

    protected function getTableQuery(): Builder
    {
        return FileManager::query()
            ->where('path', 'like', "%{$this->folderName}%")
            ->where('path', 'not like', '%backup_%')
            ->orderBy('modified_at', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back_to_folders')
                ->label('العودة للمجلدات')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.resources.file-managers.index')),
        ];
    }
} 