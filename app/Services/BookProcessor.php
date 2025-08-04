<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookInfo;
use App\Models\FileManager;
use App\Helpers\UrlHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookProcessor
{
    private $pdfExtractor;
    
    public function __construct(PdfTextExtractor $pdfExtractor)
    {
        $this->pdfExtractor = $pdfExtractor;
    }
    
    /**
     * معالجة كتاب جديد
     */
    public function processBook(FileManager $file): array
    {
        try {
            // التحقق من نوع الملف
            if (!$this->isPdfFile($file->type)) {
                return [
                    'success' => false,
                    'error' => 'File is not a PDF'
                ];
            }
            
            // استخراج النص من PDF
            $pdfPath = storage_path('app/public/' . $file->path);
            
            // إذا كان الملف في مجلد uploads، أضف المجلد للمسار
            if ($file->folder === 'uploads' && !str_contains($file->path, 'uploads/')) {
                $pdfPath = storage_path('app/public/uploads/' . $file->name);
            }
            
            // إضافة logging
            Log::info('Processing PDF file', [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'file_path' => $file->path,
                'folder' => $file->folder,
                'full_pdf_path' => $pdfPath,
                'file_exists' => file_exists($pdfPath)
            ]);
            
            $extractionResult = $this->pdfExtractor->extractTextWithProcessing($pdfPath);
            
            if (!$extractionResult['success']) {
                return $extractionResult;
            }
            
            // حفظ النص المستخرج في ملف
            $textFilePath = $this->saveExtractedText($file, $extractionResult['processed_text']);
            
            // إنشاء أو تحديث الكتاب
            $book = $this->createOrUpdateBook($file, $extractionResult);
            
            return [
                'success' => true,
                'book_id' => $book->id,
                'text_file_path' => $textFilePath,
                'extracted_text_length' => strlen($extractionResult['processed_text']),
                'pages_count' => $extractionResult['pages_count'],
                'language' => $extractionResult['language'],
                'extraction_time' => $extractionResult['extraction_time']
            ];
            
        } catch (\Exception $e) {
            Log::error('Book processing failed', [
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * حفظ النص المستخرج في ملف
     */
        private function saveExtractedText(FileManager $file, string $text): string
    {
        // إنشاء مجلد النصوص المستخرجة إذا لم يكن موجوداً
        $extractedTextsPath = storage_path('app/public/extracted_texts');
        if (!file_exists($extractedTextsPath)) {
            mkdir($extractedTextsPath, 0755, true);
        }

        // إنشاء اسم المجلد للكتاب - استخدام الاسم الجديد من path
        $fileName = pathinfo($file->path, PATHINFO_FILENAME);
        
        // البحث عن نسخة موجودة لتجنب التكرار
        $counter = 1;
        $bookFolderName = $fileName;
        
        while (file_exists($extractedTextsPath . '/' . $bookFolderName)) {
            $bookFolderName = $fileName . '-' . $counter;
            $counter++;
        }
        $bookFolderPath = $extractedTextsPath . '/' . $bookFolderName;

        // إنشاء مجلد الكتاب إذا لم يكن موجوداً
        if (!file_exists($bookFolderPath)) {
            mkdir($bookFolderPath, 0755, true);
        }

        // إنشاء اسم الملف النصي
        $textFileName = "extracted_text.txt";
        $textFilePath = $bookFolderPath . '/' . $textFileName;

        // حفظ النص في الملف
        file_put_contents($textFilePath, $text);

        // حفظ بيانات الملف في جدول file_managers
        $this->saveTextFileToDatabase($file, $textFileName, $bookFolderPath, $text);

        return 'extracted_texts/' . $bookFolderName . '/' . $textFileName;
    }

    private function saveTextFileToDatabase(FileManager $originalFile, string $textFileName, string $folderPath, string $text): void
    {
        $fileSize = strlen($text);
        $folderName = basename($folderPath);
        
        // المسار النسبي للملف - استخدام helper
        $relativePath = UrlHelper::normalizePath($folderName . '/' . $textFileName);
        
        // URL الكامل باستخدام helper
        $fileUrl = UrlHelper::generateExtractedFileUrl($relativePath);

        FileManager::create([
            'name' => $textFileName,
            'path' => $relativePath,
            'size' => $fileSize,
            'type' => 'txt',
            'url' => $fileUrl,
            'folder' => 'extracted_texts',
            'modified_at' => now(),
        ]);
    }
    
    /**
     * التحقق من أن الملف هو PDF
     */
    private function isPdfFile(?string $fileType): bool
    {
        if (!$fileType) {
            return false;
        }
        return in_array(strtolower($fileType), ['application/pdf', 'pdf']);
    }
    
    /**
     * إنشاء أو تحديث الكتاب
     */
    private function createOrUpdateBook(FileManager $file, array $extractionResult): Book
    {
        $bookIdentify = $this->generateBookIdentify($file);
        
        return Book::updateOrCreate(
            ['book_identify' => $bookIdentify],
            [
                'user_id' => auth()->id() ?? 1,
                'book_language' => $extractionResult['language']
            ]
        );
    }
    
    /**
     * إنشاء معلومات الكتاب
     */
    private function createBookInfo(Book $book, array $extractionResult): BookInfo
    {
        // استخراج عنوان الكتاب من النص (أول 100 حرف)
        $title = $this->extractTitle($extractionResult['processed_text']);
        $language = $extractionResult['language'];
        
        // تخزين النص المستخرج في book_summary
        $extractedText = $extractionResult['processed_text'];
        
        return BookInfo::updateOrCreate(
            [
                'book_id' => $book->id,
                'language' => $language
            ],
            [
                'title' => $title,
                'author' => $this->extractAuthor($extractionResult['processed_text']),
                'book_summary' => $extractedText, // النص المستخرج بالكامل
                'language' => $language
            ]
        );
    }
    
    /**
     * توليد معرف فريد للكتاب
     */
    private function generateBookIdentify(FileManager $file): string
    {
        // استخدام الاسم الجديد من path
        return pathinfo($file->path, PATHINFO_FILENAME);
    }
    
    /**
     * استخراج عنوان الكتاب
     */
    private function extractTitle(string $text): string
    {
        // البحث عن أول سطر يحتوي على نص
        $lines = explode("\n", trim($text));
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 10 && strlen($line) < 200) {
                return $line;
            }
        }
        
        return 'Untitled Book';
    }
    
    /**
     * استخراج اسم المؤلف
     */
    private function extractAuthor(string $text): string
    {
        // البحث عن كلمات مفتاحية تشير للمؤلف
        $authorKeywords = ['مؤلف', 'تأليف', 'المؤلف', 'by', 'author', 'written by'];
        
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            foreach ($authorKeywords as $keyword) {
                if (stripos($line, $keyword) !== false) {
                    $author = str_replace($keyword, '', $line);
                    $author = trim($author, ' :.،');
                    if (strlen($author) > 3) {
                        return $author;
                    }
                }
            }
        }
        
        return 'Unknown Author';
    }
    
    /**
     * توليد ملخص للكتاب
     */
    private function generateSummary(string $text): string
    {
        // أخذ أول 500 حرف كملخص
        $summary = substr($text, 0, 500);
        
        // البحث عن نهاية جملة مناسبة
        $lastPeriod = strrpos($summary, '.');
        $lastQuestion = strrpos($summary, '؟');
        $lastExclamation = strrpos($summary, '!');
        
        $endPositions = array_filter([$lastPeriod, $lastQuestion, $lastExclamation]);
        
        if (!empty($endPositions)) {
            $endPos = max($endPositions);
            $summary = substr($summary, 0, $endPos + 1);
        }
        
        return $summary;
    }
} 