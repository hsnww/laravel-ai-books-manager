<?php

namespace App\Filament\Resources\UploadManagerResource\Pages;

use App\Filament\Resources\UploadManagerResource;
use App\Models\FileManager;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CreateUploadManager extends CreateRecord
{
    protected static string $resource = UploadManagerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // حفظ معلومات الملف المرفوع في جدول file_managers
        $filePath = $data['file_path'];
        $originalName = $data['original_name'] ?? basename($filePath);
        
        // تطبيق سياسة التسمية من التطبيق السابق
        $uniqueId = $this->generateUniqueId('book');
        $newFileName = $uniqueId . '.pdf';
        
        // إعادة تسمية الملف
        $oldPath = storage_path('app/public/' . $filePath);
        $newPath = storage_path('app/public/uploads/' . $newFileName);
        
        if (file_exists($oldPath)) {
            // إنشاء مجلد uploads إذا لم يكن موجوداً
            if (!is_dir(dirname($newPath))) {
                mkdir(dirname($newPath), 0755, true);
            }
            rename($oldPath, $newPath);
        }
        
        // الحصول على حجم الملف
        $fileSize = file_exists($newPath) ? filesize($newPath) : 0;
        
        $data['name'] = $originalName; // الاسم الأصلي للعرض
        $data['path'] = 'uploads/' . $newFileName; // المسار الجديد
        $data['size'] = $fileSize;
        $data['type'] = 'pdf';
        $data['url'] = url('storage/uploads/' . $newFileName);
        $data['folder'] = 'uploads';
        $data['modified_at'] = now();
        
        // حذف file_path من البيانات لأن العمود غير موجود
        unset($data['file_path']);
        unset($data['original_name']);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('تم رفع الملف بنجاح')
            ->success();
    }

    private function generateUniqueId($prefix = 'book')
    {
        return $prefix . sprintf('%04d', rand(1, 9999)) . '_' . date('YmdHis');
    }
}
