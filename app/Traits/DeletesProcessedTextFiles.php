<?php

namespace App\Traits;

trait DeletesProcessedTextFiles
{
    /**
     * حذف الملف النصي المعالج من مجلد processed_texts
     */
    protected static function deleteProcessedTextFile($record, $processType)
    {
        try {
            // البحث عن الملف في جدول file_managers
            $fileManager = \App\Models\FileManager::where('folder', 'processed_texts')
                ->where('name', 'like', '%' . $processType . '%')
                ->where('path', 'like', '%' . $record->book_id . '%')
                ->first();
            
            if ($fileManager) {
                // حذف الملف الفعلي
                $filePath = storage_path('app/public/' . $fileManager->path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // حذف سجل الملف من قاعدة البيانات
                $fileManager->delete();
            }
        } catch (\Exception $e) {
            // تسجيل الخطأ ولكن لا توقف عملية الحذف
            \Log::error('Error deleting processed text file: ' . $e->getMessage(), [
                'process_type' => $processType,
                'book_id' => $record->book_id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 