<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileManagerService
{
    /**
     * تعديل محتوى ملف نصي
     */
    public function editFile($bookId, $filename, $content)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $filePath = $bookDir . '/' . $filename;
            
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'الملف غير موجود'];
            }
            
            // حفظ المحتوى الجديد مباشرة (بدون نسخة احتياطية)
            if (file_put_contents($filePath, $content)) {
                return ['success' => true, 'message' => "تم حفظ التعديلات في $filename بنجاح"];
            } else {
                return ['success' => false, 'message' => "فشل في حفظ التعديلات في $filename"];
            }
        } catch (\Exception $e) {
            Log::error('Error editing file: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في تعديل الملف: ' . $e->getMessage()];
        }
    }
    
    /**
     * تقسيم ملف نصي إلى عدة فصول
     */
    public function splitFile($bookId, $filename, $marker = '#####')
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $filePath = $bookDir . '/' . $filename;
            
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'الملف غير موجود'];
            }
            
            $content = file_get_contents($filePath);
            $sections = explode($marker, $content);
            $chapters = [];
            
            foreach ($sections as $index => $section) {
                $section = trim($section);
                if (!empty($section)) {
                    $lines = explode("\n", $section);
                    $title = trim($lines[0]);
                    $cleanTitle = $this->sanitizeFilename($title);
                    
                    $chapters[] = [
                        'title' => $title,
                        'content' => $section,
                        'filename' => sprintf('%04d_%s.txt', $index + 1, $cleanTitle),
                        'word_count' => str_word_count($section)
                    ];
                }
            }
            
            // إنشاء نسخة احتياطية
            $this->createBackup($bookId, $filename);
            
            // حفظ الفصول الجديدة
            foreach ($chapters as $chapter) {
                $chapterPath = $bookDir . '/' . $chapter['filename'];
                file_put_contents($chapterPath, $chapter['content']);
            }
            
            // حذف الملف الأصلي بعد التقسيم
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            return [
                'success' => true, 
                'message' => 'تم تقسيم الملف إلى ' . count($chapters) . ' فصل',
                'chapters' => $chapters
            ];
            
        } catch (\Exception $e) {
            Log::error('Error splitting file: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في تقسيم الملف: ' . $e->getMessage()];
        }
    }
    
    /**
     * دمج عدة ملفات في ملف واحد
     */
    public function mergeFiles($bookId, $filesToMerge, $newTitle)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $mergedContent = '';
            $filesWithOrder = [];
            
            // تجميع الملفات مع ترتيبها
            foreach ($filesToMerge as $filename) {
                $filePath = $bookDir . '/' . $filename;
                if (file_exists($filePath)) {
                    $order = 9999;
                    if (preg_match('/^(\d+)_/', $filename, $matches)) {
                        $order = (int)$matches[1];
                    }
                    
                    $filesWithOrder[] = [
                        'filename' => $filename,
                        'path' => $filePath,
                        'order' => $order
                    ];
                }
            }
            
            // ترتيب الملفات
            usort($filesWithOrder, function($a, $b) {
                return $a['order'] - $b['order'];
            });
            
            // دمج المحتوى
            foreach ($filesWithOrder as $fileInfo) {
                $content = file_get_contents($fileInfo['path']);
                
                // إزالة الهيدر إذا وجد
                if (strpos($content, str_repeat('=', 50)) !== false) {
                    $parts = explode(str_repeat('=', 50), $content, 2);
                    if (count($parts) > 1) {
                        $content = trim($parts[1]);
                    }
                }
                
                if (!empty($mergedContent)) {
                    $mergedContent .= "\n\n" . str_repeat('-', 50) . "\n";
                    $mergedContent .= "المصدر: " . $fileInfo['filename'] . "\n";
                    $mergedContent .= str_repeat('-', 50) . "\n\n";
                }
                
                $mergedContent .= $content;
            }
            
            // إنشاء اسم الملف المدموج
            $sanitizedTitle = $this->sanitizeFilename($newTitle);
            $minOrder = min(array_column($filesWithOrder, 'order'));
            $mergedFilename = sprintf("%04d_%s.txt", $minOrder, $sanitizedTitle);
            $mergedPath = $bookDir . '/' . $mergedFilename;
            
            // حفظ الملف المدموج
            if (file_put_contents($mergedPath, $mergedContent)) {
                // حذف الملفات الأصلية
                foreach ($filesToMerge as $filename) {
                    if ($filename !== '0000_index.txt' && file_exists($bookDir . '/' . $filename)) {
                        unlink($bookDir . '/' . $filename);
                    }
                }
                
                return ['success' => true, 'message' => 'تم دمج الملفات بنجاح'];
            }
            
        } catch (\Exception $e) {
            Log::error('Error merging files: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في دمج الملفات: ' . $e->getMessage()];
        }
    }
    
    /**
     * إعادة ترتيب الفصول
     */
    public function reorderChapters($bookId, $reorderData)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            
            // إنشاء نسخة احتياطية
            $backupDir = $bookDir . '/backup_reorder_' . date('Y-m-d_H-i-s');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $files = glob($bookDir . '/*.txt');
            foreach ($files as $file) {
                $filename = basename($file);
                copy($file, $backupDir . '/' . $filename);
            }
            
            $renamedFiles = [];
            $errors = [];
            
            foreach ($reorderData as $item) {
                $oldFilename = $item['filename'];
                $newOrder = $item['new_order'];
                
                $oldPath = $bookDir . '/' . $oldFilename;
                
                if (!file_exists($oldPath)) {
                    $errors[] = "الملف $oldFilename غير موجود";
                    continue;
                }
                
                // استخراج العنوان
                if (preg_match('/^\d+_(.+?)\.txt$/', $oldFilename, $matches)) {
                    $title = $matches[1];
                } else {
                    $title = pathinfo($oldFilename, PATHINFO_FILENAME);
                }
                
                $newFilename = sprintf("%04d_%s.txt", $newOrder, $title);
                $newPath = $bookDir . '/' . $newFilename;
                
                // التحقق من التعارض
                if (file_exists($newPath) && $oldPath !== $newPath) {
                    $counter = 1;
                    do {
                        $newFilename = sprintf("%04d_%s_%d.txt", $newOrder, $title, $counter);
                        $newPath = $bookDir . '/' . $newFilename;
                        $counter++;
                    } while (file_exists($newPath));
                }
                
                if (rename($oldPath, $newPath)) {
                    $renamedFiles[] = [
                        'old' => $oldFilename,
                        'new' => $newFilename,
                        'order' => $newOrder
                    ];
                } else {
                    $errors[] = "فشل في إعادة تسمية $oldFilename إلى $newFilename";
                }
            }
            
            return [
                'success' => true,
                'message' => 'تم إعادة ترتيب ' . count($renamedFiles) . ' فصل بنجاح',
                'renamed_files' => $renamedFiles,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            Log::error('Error reordering chapters: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في إعادة الترتيب: ' . $e->getMessage()];
        }
    }
    
    /**
     * حذف متعدد للملفات
     */
    public function deleteMultipleFiles($bookId, $filenames)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $deletedFiles = [];
            $errors = [];
            
            foreach ($filenames as $filename) {
                $filePath = $bookDir . '/' . $filename;
                
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        $deletedFiles[] = $filename;
                    } else {
                        $errors[] = "فشل في حذف $filename";
                    }
                } else {
                    $errors[] = "الملف $filename غير موجود";
                }
            }
            
            return [
                'success' => true,
                'message' => 'تم حذف ' . count($deletedFiles) . ' ملف بنجاح',
                'deleted_files' => $deletedFiles,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            Log::error('Error deleting multiple files: ' . $e->getMessage());
            return ['success' => false, 'message' => 'خطأ في حذف الملفات: ' . $e->getMessage()];
        }
    }
    
    /**
     * الحصول على قائمة ملفات الكتاب
     */
    public function getBookFiles($bookId)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $files = [];
            
            if (is_dir($bookDir)) {
                $txtFiles = glob($bookDir . '/*.txt');
                
                foreach ($txtFiles as $file) {
                    $filename = basename($file);
                    $content = file_get_contents($file);
                    $wordCount = str_word_count($content);
                    $fileSize = filesize($file);
                    
                    $files[] = [
                        'filename' => $filename,
                        'content' => $content,
                        'word_count' => $wordCount,
                        'file_size' => $fileSize,
                        'modified_at' => filemtime($file)
                    ];
                }
                
                // ترتيب الملفات حسب الرقم
                usort($files, function($a, $b) {
                    $orderA = 9999;
                    $orderB = 9999;
                    
                    if (preg_match('/^(\d+)_/', $a['filename'], $matches)) {
                        $orderA = (int)$matches[1];
                    }
                    if (preg_match('/^(\d+)_/', $b['filename'], $matches)) {
                        $orderB = (int)$matches[1];
                    }
                    
                    return $orderA - $orderB;
                });
            }
            
            return $files;
            
        } catch (\Exception $e) {
            Log::error('Error getting book files: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * إنشاء نسخة احتياطية
     */
    private function createBackup($bookId, $filename)
    {
        try {
            $bookDir = $this->getBookDirectory($bookId);
            $backupDir = $bookDir . '/backup_' . date('Y-m-d_H-i-s');
            
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filePath = $bookDir . '/' . $filename;
            if (file_exists($filePath)) {
                copy($filePath, $backupDir . '/' . $filename);
            }
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
        }
    }
    
    /**
     * الحصول على مجلد الكتاب
     */
    private function getBookDirectory($bookId)
    {
        return storage_path('app/public/extracted_texts/' . $bookId);
    }
    
    /**
     * تنظيف اسم الملف
     */
    private function sanitizeFilename($title)
    {
        // إزالة الأحرف الخاصة والمسافات
        $clean = preg_replace('/[^a-zA-Z0-9_\-\x{0600}-\x{06FF}]/u', '_', $title);
        $clean = preg_replace('/_+/', '_', $clean);
        $clean = trim($clean, '_');
        
        return $clean ?: 'untitled';
    }
    
    /**
     * تحويل المسار إلى forward slash
     */
    private function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * مزامنة ملفات المجلدات مع قاعدة البيانات
     */
    public function syncFiles()
    {
        try {
            $this->info("بدء مزامنة ملفات المجلدات...");
            
            $syncResults = [
                'extracted_texts' => $this->syncFolder('extracted_texts'),
                'processed_texts' => $this->syncFolder('processed_texts'),
                'uploads' => $this->syncFolder('uploads')
            ];
            
            $totalFiles = 0;
            $totalErrors = 0;
            
            foreach ($syncResults as $folder => $result) {
                $totalFiles += $result['files_count'];
                $totalErrors += $result['errors_count'];
            }
            
            return [
                'success' => true,
                'message' => "تم مزامنة $totalFiles ملف بنجاح. الأخطاء: $totalErrors",
                'details' => $syncResults
            ];
            
        } catch (\Exception $e) {
            Log::error('Error syncing files: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في مزامنة الملفات: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * مزامنة مجلد معين
     */
    private function syncFolder($folderName)
    {
        $filesCount = 0;
        $errorsCount = 0;
        $errors = [];
        
        try {
            $storagePath = storage_path("app/public/$folderName");
            
            if (!is_dir($storagePath)) {
                return [
                    'files_count' => 0,
                    'errors_count' => 1,
                    'errors' => ["المجلد $folderName غير موجود"]
                ];
            }
            
            // البحث عن جميع الملفات في المجلد
            $files = $this->scanDirectory($storagePath);
            
            foreach ($files as $filePath) {
                try {
                    $relativePath = str_replace($storagePath . '/', '', $filePath);
                    $fileName = basename($filePath);
                    $fileSize = filesize($filePath);
                    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                    $modifiedAt = filemtime($filePath);
                    
                    // إنشاء URL للملف
                    $url = url("storage/$folderName/$relativePath");
                    
                    // استخراج اسم مجلد الكتاب من المسار
                    $bookFolder = null;
                    if (strpos($relativePath, '\\') !== false) {
                        $pathParts = explode('\\', $relativePath);
                        $bookFolder = $pathParts[0];
                    } elseif (strpos($relativePath, '/') !== false) {
                        $pathParts = explode('/', $relativePath);
                        $bookFolder = $pathParts[0];
                    }
                    
                    // البحث عن الملف في قاعدة البيانات
                    $existingFile = \App\Models\FileManager::where('path', $relativePath)
                        ->where('folder', $folderName)
                        ->first();
                    
                    if ($existingFile) {
                        // تحديث الملف الموجود
                        $existingFile->update([
                            'name' => $fileName,
                            'size' => $fileSize,
                            'type' => $fileType,
                            'url' => $url,
                            'book_folder' => $bookFolder,
                            'modified_at' => date('Y-m-d H:i:s', $modifiedAt)
                        ]);
                    } else {
                        // إنشاء ملف جديد
                        \App\Models\FileManager::create([
                            'name' => $fileName,
                            'path' => $relativePath,
                            'size' => $fileSize,
                            'type' => $fileType,
                            'url' => $url,
                            'folder' => $folderName,
                            'book_folder' => $bookFolder,
                            'modified_at' => date('Y-m-d H:i:s', $modifiedAt)
                        ]);
                    }
                    
                    $filesCount++;
                    
                } catch (\Exception $e) {
                    $errorsCount++;
                    $errors[] = "خطأ في معالجة الملف $filePath: " . $e->getMessage();
                }
            }
            
        } catch (\Exception $e) {
            $errorsCount++;
            $errors[] = "خطأ في مزامنة مجلد $folderName: " . $e->getMessage();
        }
        
        return [
            'files_count' => $filesCount,
            'errors_count' => $errorsCount,
            'errors' => $errors
        ];
    }
    
    /**
     * مسح المجلد بشكل متكرر
     */
    private function scanDirectory($path)
    {
        $files = [];
        
        if (!is_dir($path)) {
            return $files;
        }
        
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $path . '/' . $item;
            
            if (is_dir($fullPath)) {
                // تجاهل مجلدات backup
                if (strpos($item, 'backup') !== false) {
                    continue;
                }
                
                // مسح المجلد الفرعي
                $subFiles = $this->scanDirectory($fullPath);
                $files = array_merge($files, $subFiles);
            } else {
                // إضافة الملف
                $files[] = $fullPath;
            }
        }
        
        return $files;
    }
    
    /**
     * طباعة رسالة معلومات
     */
    private function info($message)
    {
        if (app()->runningInConsole()) {
            echo $message . "\n";
        }
    }
} 