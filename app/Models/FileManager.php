<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileManager extends Model
{
    protected $table = 'file_managers';
    
    protected $fillable = [
        'name',
        'path',
        'size',
        'type',
        'url',
        'folder',
        'book_folder',
        'modified_at',
    ];

    protected $casts = [
        'modified_at' => 'datetime',
        'size' => 'integer',
    ];

    /**
     * علاقة مع معلومات الكتاب
     */
    public function bookInfo(): BelongsTo
    {
        return $this->belongsTo(BookInfo::class, 'book_folder', 'book_id');
    }

    /**
     * حذف الملف الفعلي عند حذف السجل
     */
    protected static function booted()
    {
        static::deleted(function ($fileManager) {
            // تحديد المجلد المناسب حسب نوع الملف
            $basePath = storage_path('app/public/');
            $filePath = null;
            
            // تحديد المسار الصحيح حسب نوع الملف
            if ($fileManager->type === 'pdf') {
                // ملفات PDF في مجلد uploads
                $filePath = $basePath . 'uploads/' . $fileManager->name;
            } elseif ($fileManager->type === 'txt') {
                // الملفات النصية قد تكون في extracted_texts أو processed_texts
                if (str_contains($fileManager->path, 'extracted_texts/')) {
                    $filePath = $basePath . $fileManager->path;
                } elseif (str_contains($fileManager->path, 'processed_texts/')) {
                    $filePath = $basePath . $fileManager->path;
                } else {
                    // محاولة في extracted_texts أولاً
                    $filePath = $basePath . 'extracted_texts/' . $fileManager->path;
                }
            }
            
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            } else {
                // محاولة حذف من جميع المجلدات المحتملة
                $possiblePaths = [
                    $basePath . $fileManager->path,
                    $basePath . 'extracted_texts/' . $fileManager->path,
                    $basePath . 'processed_texts/' . $fileManager->path,
                    $basePath . 'uploads/' . $fileManager->name
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                        break;
                    }
                }
            }
        });
    }
}
