<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfTextExtractor
{
    private $pythonScriptPath;
    
    public function __construct()
    {
        $this->pythonScriptPath = base_path('scripts/simple_extract.py');
    }
    
    /**
     * استخراج النص من ملف PDF
     */
    public function extractText(string $pdfPath): array
    {
        try {
            Log::info('PdfTextExtractor: Starting extraction', [
                'input_path' => $pdfPath,
                'file_exists' => file_exists($pdfPath)
            ]);
            
            // الحصول على المسار الكامل للملف
            $fullPath = $pdfPath;
            
            // إذا كان المسار نسبي، استخدم Storage::path
            if (!file_exists($pdfPath)) {
                if (!str_starts_with($pdfPath, storage_path())) {
                    $fullPath = Storage::path($pdfPath);
                    Log::info('PdfTextExtractor: Using Storage::path', ['storage_path' => $fullPath]);
                }
            }
            
            // إذا كان المسار يحتوي على /private/، استبدله بـ /public/
            if (strpos($fullPath, '/private/') !== false) {
                $fullPath = str_replace('/private/', '/public/', $fullPath);
                Log::info('PdfTextExtractor: Fixed private path', ['fixed_path' => $fullPath]);
            }

            // التحقق من وجود الملف الفعلي
            if (!file_exists($fullPath)) {
                Log::error('PdfTextExtractor: File not found', ['full_path' => $fullPath]);
                throw new \Exception("PDF file not found at path: {$fullPath}");
            }
            
            Log::info('PdfTextExtractor: File found, proceeding with extraction', ['full_path' => $fullPath]);
            
            // التحقق من وجود سكريبت Python
            if (!file_exists($this->pythonScriptPath)) {
                throw new \Exception("Python script not found: {$this->pythonScriptPath}");
            }
            
            // تشغيل سكريبت Python
            $command = "py \"{$this->pythonScriptPath}\" \"{$fullPath}\"";
            Log::info('PdfTextExtractor: Executing command', ['command' => $command]);
            
            $output = shell_exec($command . " 2>&1");
            
            if ($output === null) {
                throw new \Exception("Failed to execute Python script - no output returned");
            }
            
            Log::info('PdfTextExtractor: Python script output', ['output_length' => strlen($output), 'output_preview' => substr($output, 0, 200)]);
            
            // تنظيف النص من الأحرف غير الصحيحة
            $output = preg_replace('/[\x00-\x1F\x7F]/', '', $output);
            
            // معالجة الترميز للنص العربي
            $output = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
            $output = iconv('UTF-8', 'UTF-8//IGNORE', $output);
            
            $result = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from Python script: " . json_last_error_msg() . " - Output: " . substr($output, 0, 200));
            }
            
            return [
                'success' => true,
                'text' => $result['text'] ?? '',
                'pages_count' => $result['pages_processed'] ?? 0,
                'language' => $result['language'] ?? 'ar',
                'extraction_time' => $result['extraction_time'] ?? 0,
                'ocr_used' => $result['ocr_used'] ?? false
            ];
            
        } catch (\Exception $e) {
            Log::error('PDF text extraction failed', [
                'file' => $pdfPath,
                'full_path' => $fullPath ?? 'unknown',
                'python_script' => $this->pythonScriptPath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * استخراج النص مع معالجة إضافية
     */
    public function extractTextWithProcessing(string $pdfPath): array
    {
        $result = $this->extractText($pdfPath);
        
        if (!$result['success']) {
            return $result;
        }
        
        // معالجة إضافية للنص العربي
        $text = $this->processArabicText($result['text']);
        
        return [
            'success' => true,
            'original_text' => $result['text'],
            'processed_text' => $text,
            'pages_count' => $result['pages_count'],
            'language' => $result['language'],
            'extraction_time' => $result['extraction_time'],
            'ocr_used' => $result['ocr_used']
        ];
    }
    
    /**
     * معالجة النص العربي
     */
    private function processArabicText(string $text): string
    {
        // إزالة الأحرف غير المرغوبة مع الحفاظ على علامات التنسيق
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s\.\,\!\?\:\;\(\)\[\]\{\}\-\_\'\"\#\📄]+/u', ' ', $text);
        
        // تنظيف المسافات المتعددة مع الحفاظ على فواصل الأسطر
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        // إزالة المسافات في بداية ونهاية النص
        $text = trim($text);
        
        return $text;
    }
} 