<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\EnhancedText;
use App\Models\SummarizedText;
use App\Models\TranslatedText;
use App\Models\FormattingImprovedText;
use App\Models\LanguageImprovedText;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TextProcessor
{
    private $aiPromptService;
    
    public function __construct(AiPromptService $aiPromptService = null)
    {
        $this->aiPromptService = $aiPromptService ?? new AiPromptService();
    }
    
    /**
     * معالجة النص بالذكاء الاصطناعي وحفظه في مجلد processed_texts
     */
    public function processText(FileManager $textFile, string $processType, string $targetLanguage = 'arabic'): array
    {
        try {
            
            // قراءة النص من الملف
            $textPath = storage_path('app/public/' . $textFile->folder . '/' . $textFile->path);
            if (!file_exists($textPath)) {
                return [
                    'success' => false,
                    'error' => 'Text file not found'
                ];
            }
            
            $originalText = file_get_contents($textPath);
            
            // معالجة النص بالذكاء الاصطناعي
            $processedText = $this->processWithAI($originalText, $processType, $targetLanguage);
            
            if (!$processedText['success']) {
                return $processedText;
            }
            
            // حفظ النص المعالج في مجلد processed_texts
            $processedFilePath = $this->saveProcessedText($textFile, $processedText['text'], $processType, $targetLanguage);
            
            // حفظ في قاعدة البيانات
            $this->saveToDatabase($textFile, $processedText['text'], $processType, $targetLanguage);
            
            return [
                'success' => true,
                'processed_file_path' => $processedFilePath,
                'original_length' => strlen($originalText),
                'processed_length' => strlen($processedText['text']),
                'process_type' => $processType,
                'target_language' => $targetLanguage
            ];
            
        } catch (\Exception $e) {
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * معالجة النص بالذكاء الاصطناعي
     */
    private function processWithAI(string $text, string $processType, string $targetLanguage): array
    {
        try {
            // الحصول على البرومبت المناسب
            $prompt = $this->aiPromptService->getPrompt($processType, $targetLanguage);
            
            if (!$prompt) {
                return [
                    'success' => false,
                    'error' => 'No prompt found for process type: ' . $processType
                ];
            }
            
            // استبدال المتغيرات في البرومبت
            $promptText = str_replace(
                ['{text}', '{language}'],
                [$text, $targetLanguage],
                $prompt->prompt_text
            );
            
            // إرسال الطلب للذكاء الاصطناعي (هنا يمكن استخدام OpenAI أو أي خدمة أخرى)
            $response = $this->callAIService($promptText);
            
            if (!$response['success']) {
                return $response;
            }
            
            return [
                'success' => true,
                'text' => $response['text']
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * استدعاء خدمة الذكاء الاصطناعي
     */
    private function callAIService(string $prompt): array
    {
        // هنا يمكن استخدام OpenAI API أو أي خدمة أخرى
        // حالياً سنقوم بمحاكاة الاستجابة
        
        try {
            // في التطبيق الحقيقي، استخدم OpenAI API
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            // ])->post('https://api.openai.com/v1/chat/completions', [
            //     'model' => 'gpt-3.5-turbo',
            //     'messages' => [
            //         ['role' => 'user', 'content' => $prompt]
            //     ]
            // ]);
            
            // محاكاة الاستجابة للاختبار
            $processedText = $this->simulateAIResponse($prompt);
            
            return [
                'success' => true,
                'text' => $processedText
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * محاكاة استجابة الذكاء الاصطناعي للاختبار
     */
    private function simulateAIResponse(string $prompt): string
    {
        // استخراج النص الأصلي من البرومبت
        if (preg_match('/{text}(.*?){language}/s', $prompt, $matches)) {
            $originalText = $matches[1] ?? '';
        } else {
            // إذا لم نجد النص بين المتغيرات، نأخذ النص كاملاً
            $originalText = $prompt;
        }
        
        // تنظيف النص من المتغيرات
        $originalText = str_replace(['{text}', '{language}', 'arabic'], '', $originalText);
        
        // معالجة النص حسب نوع المعالجة المطلوب
        if (strpos($prompt, 'تحسين') !== false || strpos($prompt, 'enhance') !== false) {
            // تحسين النص
            $processedText = $this->enhanceText($originalText);
        } elseif (strpos($prompt, 'تلخيص') !== false || strpos($prompt, 'summarize') !== false) {
            // تلخيص النص
            $processedText = $this->summarizeText($originalText);
        } elseif (strpos($prompt, 'ترجمة') !== false || strpos($prompt, 'translate') !== false) {
            // ترجمة النص
            $processedText = $this->translateText($originalText);
        } elseif (strpos($prompt, 'تنسيق') !== false || strpos($prompt, 'format') !== false) {
            // تحسين التنسيق
            $processedText = $this->improveFormatting($originalText);
        } elseif (strpos($prompt, 'لغة') !== false || strpos($prompt, 'language') !== false) {
            // تحسين اللغة
            $processedText = $this->improveLanguage($originalText);
        } else {
            // معالجة افتراضية
            $processedText = $this->enhanceText($originalText);
        }
        
        return $processedText;
    }
    
    /**
     * تحسين النص
     */
    private function enhanceText(string $text): string
    {
        // إزالة المسافات الزائدة
        $text = preg_replace('/\s+/', ' ', $text);
        
        // تحسين علامات الترقيم
        $text = str_replace([' .', ' ,', ' :', ' ;'], ['.', ',', ':', ';'], $text);
        
        // إزالة الأسطر الفارغة المتكررة
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        
        // تحسين تنسيق الفقرات
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * تلخيص النص
     */
    private function summarizeText(string $text): string
    {
        // تقسيم النص إلى فقرات
        $paragraphs = explode("\n\n", $text);
        
        // أخذ أول 3 فقرات كملخص
        $summaryParagraphs = array_slice($paragraphs, 0, 3);
        
        // دمج الفقرات
        $summary = implode("\n\n", $summaryParagraphs);
        
        // إضافة عنوان للملخص
        $summary = "=== ملخص النص ===\n\n" . $summary;
        
        return $summary;
    }
    
    /**
     * ترجمة النص (محاكاة)
     */
    private function translateText(string $text): string
    {
        // محاكاة الترجمة - إضافة علامة الترجمة
        return "=== النص المترجم ===\n\n" . $text;
    }
    
    /**
     * تحسين التنسيق
     */
    private function improveFormatting(string $text): string
    {
        // تحسين تنسيق العناوين
        $text = preg_replace('/^(.+)$/m', function($matches) {
            $line = trim($matches[1]);
            if (strlen($line) < 100 && !preg_match('/[.!?]$/', $line)) {
                return "## " . $line;
            }
            return $line;
        }, $text);
        
        // تحسين تنسيق القوائم
        $text = preg_replace('/^\s*[-•]\s*/m', '- ', $text);
        
        return $text;
    }
    
    /**
     * تحسين اللغة
     */
    private function improveLanguage(string $text): string
    {
        // تحسين الأخطاء الإملائية الشائعة
        $replacements = [
            'هاذا' => 'هذا',
            'هاذه' => 'هذه',
            'هاذو' => 'هؤلاء',
            'هاذي' => 'هذه',
            'علي' => 'على',
            'الي' => 'إلى',
            'فية' => 'فيه',
            'فيهة' => 'فيها',
        ];
        
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        
        return $text;
    }
    
    /**
     * حفظ النص المعالج في مجلد processed_texts
     */
    private function saveProcessedText(FileManager $originalFile, string $processedText, string $processType, string $targetLanguage): string
    {
        // إنشاء مجلد processed_texts إذا لم يكن موجوداً
        $processedTextsPath = storage_path('app/public/processed_texts');
        
        if (!file_exists($processedTextsPath)) {
            mkdir($processedTextsPath, 0755, true);
        }
        
        // إنشاء اسم المجلد للكتاب
        $fileName = pathinfo($originalFile->name, PATHINFO_FILENAME);
        $timestamp = now()->format('YmdHis');
        $bookFolderName = "{$fileName}_{$timestamp}";
        $bookFolderPath = $processedTextsPath . '/' . $bookFolderName;
        
        // إنشاء مجلد الكتاب إذا لم يكن موجوداً
        if (!file_exists($bookFolderPath)) {
            mkdir($bookFolderPath, 0755, true);
        }
        
        // إنشاء اسم الملف المعالج
        $processedFileName = "processed_{$processType}.txt";
        $processedFilePath = $bookFolderPath . '/' . $processedFileName;
        
        // حفظ النص المعالج في الملف
        $bytesWritten = file_put_contents($processedFilePath, $processedText);
        
        if ($bytesWritten === false) {
            throw new \Exception('Failed to write processed text file');
        }
        
        // حفظ بيانات الملف في جدول file_managers
        $this->saveProcessedFileToDatabase($originalFile, $processedFileName, $bookFolderPath, $processedText, $processType);
        
        return 'processed_texts/' . $bookFolderName . '/' . $processedFileName;
    }
    
    /**
     * حفظ ملف النص المعالج في قاعدة البيانات
     */
    private function saveProcessedFileToDatabase(FileManager $originalFile, string $processedFileName, string $folderPath, string $processedText, string $processType): void
    {
        $fileSize = strlen($processedText);
        $folderName = basename($folderPath);
        
        try {
            FileManager::create([
                'name' => $processedFileName,
                'url' => 'processed_texts/' . $folderName . '/' . $processedFileName,
                'size' => $fileSize,
                'type' => 'processed_text',
                'book_id' => $originalFile->book_id,
                'folder_path' => $folderPath,
                'process_type' => $processType,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * حفظ في قاعدة البيانات حسب نوع المعالجة
     */
    private function saveToDatabase(FileManager $originalFile, string $processedText, string $processType, string $targetLanguage): void
    {
        $bookId = $this->getBookIdFromFile($originalFile);
        
        // استخراج العنوان من النص المعالج
        $title = $this->extractTitleFromProcessedText($processedText);
        
        // تنظيف النص من كلمات "العنوان:" و "الملخص:" و "النص:"
        $cleanedText = $this->cleanProcessedText($processedText);
        
        switch ($processType) {
            case 'enhance':
                EnhancedText::create([
                    'book_id' => $bookId,
                    'original_file' => $originalFile->name,
                    'title' => $title,
                    'enhanced_text' => $cleanedText,
                    'target_language' => $targetLanguage,
                    'processing_date' => now(),
                ]);
                break;
                
            case 'summarize':
                SummarizedText::create([
                    'book_id' => $bookId,
                    'original_file' => $originalFile->name,
                    'title' => $title,
                    'summarized_text' => $cleanedText,
                    'target_language' => $targetLanguage,
                    'summary_length' => strlen($cleanedText),
                    'processing_date' => now(),
                ]);
                break;
                
            case 'translate':
                TranslatedText::create([
                    'book_id' => $bookId,
                    'original_file' => $originalFile->name,
                    'title' => $title,
                    'translated_text' => $cleanedText,
                    'target_language' => $targetLanguage,
                    'processing_date' => now(),
                ]);
                break;
                
            case 'improve_format':
                FormattingImprovedText::create([
                    'book_id' => $bookId,
                    'original_file' => $originalFile->name,
                    'title' => $title,
                    'improved_text' => $cleanedText,
                    'target_language' => $targetLanguage,
                    'processing_date' => now(),
                ]);
                break;
                
            case 'improve_language':
                LanguageImprovedText::create([
                    'book_id' => $bookId,
                    'original_file' => $originalFile->name,
                    'title' => $title,
                    'language_improved_text' => $cleanedText,
                    'target_language' => $targetLanguage,
                    'processing_date' => now(),
                ]);
                break;
        }
    }
    
    /**
     * استخراج العنوان من النص المعالج
     */
    private function extractTitleFromProcessedText(string $processedText): string
    {
        // البحث عن العنوان في بداية النص
        $lines = explode("\n", $processedText);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // البحث عن العنوان بعد "العنوان:" أو "Title:"
            if (preg_match('/^(العنوان|Title):\s*(.+)$/i', $line, $matches)) {
                return trim($matches[2]);
            }
            
            // البحث عن العنوان في السطر الأول إذا كان قصيراً (أقل من 100 حرف)
            if (strlen($line) > 0 && strlen($line) < 100 && !preg_match('/^(النص|Text|الملخص|Summary):/i', $line)) {
                return $line;
            }
        }
        
        // إذا لم يتم العثور على عنوان، استخدم اسم الملف الأصلي
        return 'نص معالج';
    }
    
    /**
     * تنظيف النص من كلمات "العنوان:" و "الملخص:" و "النص:"
     */
    private function cleanProcessedText(string $processedText): string
    {
        $lines = explode("\n", $processedText);
        $cleanedLines = [];
        $skipNextLine = false;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // تخطي السطور التي تحتوي على "العنوان:" أو "Title:"
            if (preg_match('/^(العنوان|Title):\s*(.+)$/i', $trimmedLine)) {
                continue;
            }
            
            // تخطي السطور التي تحتوي على "الملخص:" أو "Summary:" أو "النص:" أو "Text:"
            if (preg_match('/^(الملخص|Summary|النص|Text):\s*(.+)$/i', $trimmedLine)) {
                // إضافة المحتوى بعد النقطتين إذا كان موجوداً
                if (preg_match('/^(الملخص|Summary|النص|Text):\s*(.+)$/i', $trimmedLine, $matches)) {
                    $cleanedLines[] = trim($matches[2]);
                }
                continue;
            }
            
            // إضافة السطر إذا لم يكن فارغاً
            if (!empty($trimmedLine)) {
                $cleanedLines[] = $trimmedLine;
            }
        }
        
        return implode("\n", $cleanedLines);
    }
    
    /**
     * الحصول على معرف الكتاب من الملف
     */
    private function getBookIdFromFile(FileManager $file): int
    {
        // البحث عن الكتاب المرتبط بالملف
        $book = \App\Models\Book::where('book_identify', 'like', '%' . pathinfo($file->name, PATHINFO_FILENAME) . '%')->first();
        
        return $book ? $book->id : 1; // استخدام معرف افتراضي إذا لم يتم العثور على الكتاب
    }
} 