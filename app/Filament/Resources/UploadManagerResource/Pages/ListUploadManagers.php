<?php

namespace App\Filament\Resources\UploadManagerResource\Pages;

use App\Filament\Resources\UploadManagerResource;
use App\Models\FileManager;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Services\BookProcessor;
use App\Services\PdfTextExtractor;

class ListUploadManagers extends ListRecords
{
    protected static string $resource = UploadManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('رفع ملف PDF')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function extractText(FileManager $record)
    {
        try {
            // استخدام النظام الجديد
            $processor = new BookProcessor(app(PdfTextExtractor::class));
            $result = $processor->processBook($record);

            if ($result['success']) {
                Notification::make()
                    ->title('تم استخراج النصوص بنجاح')
                    ->body("تم حفظ النص في: {$result['text_file_path']}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('فشل في استخراج النصوص')
                    ->body($result['error'])
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في استخراج النصوص')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
