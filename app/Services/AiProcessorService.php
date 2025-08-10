<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AiPrompt;
use App\Models\ProcessingHistory;
use App\Models\EnhancedText;
use App\Models\TranslatedText;
use App\Models\SummarizedText;
use App\Models\FormattingImprovedText;
use App\Models\BookInfo;
use App\Models\Book;
use App\Helpers\LanguageHelper;

class AiProcessorService
{
    private $apiKey;
    private $apiUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->apiUrl = config('services.gemini.api_url');
    }
    
    /**
     * Get book ID from book_identify
     */
    private function getBookId($bookIdentify)
    {
        $book = Book::where('book_identify', $bookIdentify)->first();
        if (!$book) {
            // Create new book record if not exists
            $book = Book::create([
                'book_identify' => $bookIdentify,
                'book_language' => 'arabic', // default
                'user_id' => auth()->id() ?? 1, // default user
            ]);
        }
        return $book->id;
    }
    
    /**
     * Process text for AI trial (without saving to database)
     */
    public function processTextForTrial($originalText, $processingType, $targetLanguage)
    {
        try {
            // Convert single processing type to array for buildPrompt
            $processingOptions = [$processingType];
            
            // Build prompt
            $prompt = $this->buildPrompt($processingOptions, $originalText, $targetLanguage);
            
            // Call Gemini API
            $response = $this->callGeminiAPI($prompt);
            
            if ($response['success']) {
                return $response['text'];
            } else {
                throw new \Exception('فشل في معالجة النص: ' . ($response['error'] ?? 'خطأ غير معروف'));
            }
            
        } catch (\Exception $e) {
            Log::error('AI Trial Processing Error', [
                'error' => $e->getMessage(),
                'processing_type' => $processingType,
                'target_language' => $targetLanguage,
                'text_length' => strlen($originalText)
            ]);
            
            throw $e;
        }
    }

    /**
     * Process text with AI based on the specified options
     */
        public function processText($originalText, $processingOptions, $targetLanguage, $bookIdentify, $originalFile)
    {
        $startTime = microtime(true);
        
        Log::info('=== AI PROCESSING START ===', [
            'book_identify' => $bookIdentify,
            'original_file' => $originalFile,
            'processing_options' => $processingOptions,
            'target_language' => $targetLanguage,
            'text_length' => strlen($originalText)
        ]);
        
        try {
            // Get actual book ID from book_identify
            $bookId = $this->getBookId($bookIdentify);
            
            Log::info('Book ID retrieved', [
                'book_identify' => $bookIdentify,
                'book_id' => $bookId
            ]);
            
            // Log processing start (minimal logging)
            $this->logProcessingHistory($bookId, $originalFile, $processingOptions, $targetLanguage, 'in_progress');
            
            // Build prompt
            $prompt = $this->buildPrompt($processingOptions, $originalText, $targetLanguage);
            
            Log::info('Prompt built successfully', [
                'prompt_length' => strlen($prompt),
                'processing_options' => $processingOptions
            ]);
            
            // Call Gemini API
            $response = $this->callGeminiAPI($prompt);
            
            if ($response['success']) {
                Log::info('Gemini API call successful', [
                    'response_length' => strlen($response['text']),
                    'processing_options' => $processingOptions
                ]);
                
                // Save results
                $this->saveResults($response['text'], $processingOptions, $targetLanguage, $bookId, $originalFile);
                
                Log::info('Results saved successfully', [
                    'book_id' => $bookId,
                    'processing_options' => $processingOptions
                ]);
                
                // Log successful processing
                $processingTime = round((microtime(true) - $startTime) * 1000);
                $this->logProcessingHistory($bookId, $originalFile, $processingOptions, $targetLanguage, 'success', null, $processingTime);
                
                Log::info('=== AI PROCESSING COMPLETED SUCCESSFULLY ===', [
                    'processing_time_ms' => $processingTime,
                    'book_id' => $bookId
                ]);
                
                return [
                    'success' => true,
                    'text' => $response['text'],
                    'processing_time' => $processingTime
                ];
            } else {
                Log::error('Gemini API call failed', [
                    'error' => $response['error'],
                    'processing_options' => $processingOptions
                ]);
                
                // Log failed processing
                $this->logProcessingHistory($bookId, $originalFile, $processingOptions, $targetLanguage, 'failed', $response['error']);
                
                return $response;
            }
            
        } catch (\Exception $e) {
            Log::error('AI Processing Error: ' . $e->getMessage());
            
            // Get book ID for error logging
            $bookId = $this->getBookId($bookIdentify);
            
            // Log failed processing
            $this->logProcessingHistory($bookId, $originalFile, $processingOptions, $targetLanguage, 'failed', $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'خطأ في معالجة النص: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Build prompt from database prompts
     */
    private function buildPrompt($processingOptions, $originalText, $targetLanguage)
    {
        $combinedPrompt = '';
        
        // Get prompts from database - use any available prompt regardless of language
        foreach ($processingOptions as $option) {
            Log::info('Looking for prompt with type: ' . $option);
            
            // Handle fallback for old improve_formatting requests
            $searchOption = $option;
            if ($option === 'improve_formatting') {
                $searchOption = 'improve_format';
                Log::info('Using fallback: improve_formatting -> improve_format');
            }
            
            $prompt = AiPrompt::active()
                ->byType($searchOption)
                ->first(); // Get any available prompt regardless of language
                
            if ($prompt) {
                Log::info('Found prompt: ' . $prompt->name . ' (ID: ' . $prompt->id . ')');
                // Replace {language} placeholder with the target language
                $promptText = str_replace('{language}', $targetLanguage, $prompt->prompt_text);
                $combinedPrompt .= $promptText . "\n\n";
            } else {
                Log::error('No prompt found for type: ' . $searchOption);
            }
        }
        
        // Add the original text
        $combinedPrompt .= "النص الأصلي:\n" . $originalText . "\n\n";
        
        // Add additional instructions
        $additionalInstructions = "\n\n**تعليمات مهمة إضافية:**\n";
        $additionalInstructions .= "- اكتب النص مباشرة باللغة المطلوبة دون مقدمة أو شرح\n";
        $additionalInstructions .= "- لا تضيف عبارات مثل 'Here is the processed text' أو 'This is the translation'\n";
        $additionalInstructions .= "- لا تشرح ما فعلت، فقط اكتب النص المطلوب\n";
        $additionalInstructions .= "- ابدأ النص مباشرة دون أي مقدمة أو عنوان\n";
        
        $combinedPrompt .= $additionalInstructions;
        

        
        return $combinedPrompt;
    }
    
    /**
     * Call Gemini API
     */
    private function callGeminiAPI($prompt)
    {
        try {
            // Clean the prompt before sending
            $cleanPrompt = $this->cleanText($prompt);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->apiKey
            ])->timeout(120)->post($this->apiUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $cleanPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        'success' => true,
                        'text' => $data['candidates'][0]['content']['parts'][0]['text']
                    ];
                }
            }
            
            Log::error('Gemini API Error: ' . $response->body());
            
            return ['success' => false, 'error' => 'خطأ في استدعاء API: ' . $response->status()];
            
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'خطأ في الاتصال: ' . $e->getMessage()];
        }
    }
    
    /**
     * Save processing results to database
     */
    private function saveResults($processedText, $processingOptions, $targetLanguage, $bookId, $originalFile)
    {
        Log::info('=== SAVE RESULTS START ===', [
            'book_id' => $bookId,
            'processing_options' => $processingOptions,
            'target_language' => $targetLanguage,
            'original_file' => $originalFile,
            'processed_text_length' => strlen($processedText)
        ]);
        
        // تحقق من وجود سجل في books_info وإنشاؤه تلقائياً إذا لم يوجد
        $this->ensureBookInfoExists($bookId, $originalFile, $processedText, $targetLanguage);
        
        // استخراج العنوان من النص المعالج
        $title = $this->extractTitleFromProcessedText($processedText);
        
        // تنظيف النص من كلمات "العنوان:" و "الملخص:" و "النص:"
        $cleanedText = $this->cleanProcessedText($processedText);
        
        foreach ($processingOptions as $option) {
            Log::info('Processing option', [
                'option' => $option,
                'book_id' => $bookId
            ]);
            
            switch ($option) {
                case 'enhance':
                    Log::info('Creating EnhancedText record');
                    EnhancedText::create([
                        'book_id' => $bookId,
                        'original_file' => $originalFile,
                        'title' => $title,
                        'enhanced_text' => $cleanedText,
                        'target_language' => $targetLanguage,
                    ]);
                    Log::info('EnhancedText created successfully');
                    
                    // Save processed text as file
                    $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'enhance', $targetLanguage);
                    break;
                    
                case 'translate':
                    Log::info('Creating TranslatedText record');
                    TranslatedText::create([
                        'book_id' => $bookId,
                        'original_file' => $originalFile,
                        'title' => $title,
                        'translated_text' => $cleanedText,
                        'source_language' => 'auto', // Will be detected
                        'target_language' => $targetLanguage,
                    ]);
                    Log::info('TranslatedText created successfully');
                    
                    // Save processed text as file
                    $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'translate', $targetLanguage);
                    break;
                    
                case 'summarize':
                    Log::info('Creating SummarizedText record');
                    SummarizedText::create([
                        'book_id' => $bookId,
                        'original_file' => $originalFile,
                        'title' => $title,
                        'summarized_text' => $cleanedText,
                        'target_language' => $targetLanguage,
                        'summary_length' => 'medium',
                        'processing_date' => now(),
                    ]);
                    Log::info('SummarizedText created successfully');
                    
                    // Save processed text as file
                    $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'summarize', $targetLanguage);
                    break;
                    

                    
                case 'improve_format':
                case 'improve_formatting': // Fallback for old requests
                    Log::info('Creating FormattingImprovedText record');
                    FormattingImprovedText::create([
                        'book_id' => $bookId,
                        'original_file' => $originalFile,
                        'title' => $title,
                        'improved_text' => $cleanedText,
                        'target_language' => $targetLanguage,
                    ]);
                    Log::info('FormattingImprovedText created successfully');
                    
                    // Save processed text as file
                    $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'improve_format', $targetLanguage);
                    break;
                    
                case 'extract_info':
                    Log::info('Starting book info extraction');
                    // Extract book info from processed text
                    $this->extractBookInfo($processedText, $bookId, $targetLanguage);
                    Log::info('Book info extraction completed');
                    
                    // Also save the processed text as a file
                    Log::info('Saving extracted book info as text file');
                    $this->saveProcessedTextFile($processedText, $bookId, $originalFile, 'extract_info', $targetLanguage);
                    Log::info('Book info text file saved');
                    break;
                    
                case 'blog_article':
                    Log::info('Creating BlogArticle record');
                    
                    try {
                        // Calculate word count
                        $wordCount = str_word_count(strip_tags($cleanedText));
                        
                        // Extract SEO keywords from the article (enhanced extraction)
                        $seoKeywords = $this->extractSeoKeywords($cleanedText);
                        
                        // Extract title from the processed text (before cleaning)
                        $extractedTitle = $this->extractTitleFromProcessedText($processedText);
                        
                        // Use extracted title if it's not empty and not default
                        if (!empty($extractedTitle) && $extractedTitle !== 'نص معالج') {
                            $title = $extractedTitle;
                        }
                        
                        // If still empty, try to extract from first line of cleaned text
                        if (empty($title) || $title === 'نص معالج') {
                            $lines = explode("\n", $cleanedText);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (!empty($line) && strlen($line) < 150) {
                                    $title = $line;
                                    break;
                                }
                            }
                        }
                        
                        // Final fallback
                        if (empty($title) || $title === 'نص معالج') {
                            $title = 'مقال مدونة';
                        }
                        
                        Log::info('Title extraction result', [
                            'extracted_title' => $extractedTitle,
                            'final_title' => $title
                        ]);
                        
                        // Create BlogArticle record
                        $blogArticle = \App\Models\BlogArticle::create([
                            'book_id' => $bookId,
                            'original_file' => $originalFile,
                            'title' => $title,
                            'article_content' => $cleanedText,
                            'target_language' => $targetLanguage,
                            'article_type' => 'blog',
                            'word_count' => $wordCount,
                            'seo_keywords' => $seoKeywords,
                            'status' => 'draft',
                            'processing_date' => now(),
                        ]);
                        
                        Log::info('BlogArticle created successfully', [
                            'blog_article_id' => $blogArticle->id,
                            'title' => $title,
                            'word_count' => $wordCount,
                            'seo_keywords_count' => count(explode(',', $seoKeywords))
                        ]);
                        
                        // Also save as text file for backup
                        $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'blog_article', $targetLanguage);
                        Log::info('Blog article text file saved');
                        
                    } catch (\Exception $e) {
                        Log::error('Error creating BlogArticle: ' . $e->getMessage(), [
                            'book_id' => $bookId,
                            'original_file' => $originalFile,
                            'error' => $e->getMessage()
                        ]);
                        
                        // Still save as text file even if database save fails
                        $this->saveProcessedTextFile($cleanedText, $bookId, $originalFile, 'blog_article', $targetLanguage);
                        Log::info('Blog article text file saved as backup');
                    }
                    break;
            }
        }
        
        Log::info('=== SAVE RESULTS COMPLETED ===', [
            'book_id' => $bookId,
            'processing_options' => $processingOptions
        ]);
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
        
        // البحث عن العنوان في المقالات (السطر الأول الذي لا يحتوي على كلمات مفتاحية)
        $firstNonEmptyLine = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $firstNonEmptyLine = $line;
                break;
            }
        }
        
        if ($firstNonEmptyLine) {
            // إذا كان السطر الأول قصيراً ولا يحتوي على كلمات مفتاحية، فهو العنوان
            if (strlen($firstNonEmptyLine) < 150 && 
                !preg_match('/^(مقدمة|introduction|عنوان|title|الكتاب|book|المؤلف|author|سنة|year|الناشر|publisher|عدد|pages|التصنيف|category|الملخص|summary|النص|text|الفصل|chapter|\*\*|#|##|###)/i', $firstNonEmptyLine)) {
                return $firstNonEmptyLine;
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
            
            // تنظيف المقالات من العناوين المكررة في البداية
            if (preg_match('/^(مقدمة|introduction|عنوان|title|الكتاب|book|المؤلف|author|سنة|year|الناشر|publisher|عدد|pages|التصنيف|category|الملخص|summary|النص|text|الفصل|chapter|\*\*|#|##|###)/i', $trimmedLine)) {
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
     * Extract book information from processed text
     */
    private function extractBookInfo($processedText, $bookId, $language)
    {
        Log::info('=== EXTRACT BOOK INFO START ===', [
            'book_id' => $bookId,
            'language' => $language,
            'processed_text_length' => strlen($processedText)
        ]);
        
        // Get extraction prompt from database
        $extractionPrompt = '';
        $prompt = AiPrompt::active()
            ->byType('extract_info')
            ->first();
            
        Log::info('Prompt lookup result', [
            'prompt_found' => $prompt ? true : false,
            'prompt_id' => $prompt ? $prompt->id : null,
            'prompt_type' => $prompt ? $prompt->prompt_type : null
        ]);
            
        if ($prompt) {
            // Replace placeholders in the prompt
            $extractionPrompt = str_replace(
                ['{language}', '{text}'],
                [$language, substr($processedText, 0, 2000)], // زيادة عدد الأحرف من 500 إلى 2000
                $prompt->prompt_text
            );
            
            Log::info('Using database prompt', [
                'prompt_length' => strlen($extractionPrompt)
            ]);
        } else {
            // Fallback prompt if not found in database
            $extractionPrompt = "أنت متخصص في استخراج معلومات الكتب. مهمتك إنشاء معلومات الكتاب من النص التالي:\n\n";
            $extractionPrompt .= "المعلومات المطلوبة:\n";
            $extractionPrompt .= "- عنوان الكتاب\n";
            $extractionPrompt .= "- اسم المؤلف\n";
            $extractionPrompt .= "- ملخص مختصر في حدود 200 كلمة\n\n";
            $extractionPrompt .= "تعليمات الاستخراج:\n";
            $extractionPrompt .= "- أنشئ جميع معلومات الكتاب باللغة " . $language . " بما في ذلك عنوان الكتاب واسم المؤلف\n";
            $extractionPrompt .= "- إذا لم تجد معلومة، اكتب \"غير محدد\" أو ما يقابلها باللغة المحددة\n";
            $extractionPrompt .= "- اكتب المعلومات مباشرة باللغة المطلوبة\n";
            $extractionPrompt .= "- اكتب ملخصاً مكتملاً ومفيداً دون انقطاع أو عبارات مثل \"بقية الملخص غير متوفرة\" أو \"يمكن استنتاج\"\n";
            $extractionPrompt .= "- تأكد من أن الملخص ينتهي بجملة مكتملة ومنطقية\n";
            $extractionPrompt .= "- لا تستخدم علامات الحذف (...) في نهاية الملخص\n\n";
            $extractionPrompt .= "النص المراد استخراج المعلومات منه:\n" . substr($processedText, 0, 2000) . "\n\n";
            $extractionPrompt .= "التنسيق المطلوب:\n";
            $extractionPrompt .= "العنوان: [عنوان الكتاب]\n";
            $extractionPrompt .= "المؤلف: [اسم المؤلف]\n";
            $extractionPrompt .= "الملخص: [ملخص مختصر ومكتمل]\n";
            
            Log::info('Using fallback prompt', [
                'prompt_length' => strlen($extractionPrompt)
            ]);
        }
        
        try {
            Log::info('Calling Gemini API for book info extraction');
            $response = $this->callGeminiAPI($extractionPrompt);
            
            if ($response['success']) {
                $extractedText = $response['text'];
                
                Log::info('Gemini API response received', [
                    'response_length' => strlen($extractedText),
                    'response_sample' => substr($extractedText, 0, 200)
                ]);
                
                $lines = explode("\n", $extractedText);
                
                $title = '';
                $author = '';
                $summary = '';
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // Extract title - support multiple languages and formats
                    if (strpos($line, 'العنوان:') !== false || strpos($line, 'Title:') !== false) {
                        $title = trim(str_replace(['العنوان:', 'Title:'], '', $line));
                        // Remove brackets if present
                        $title = trim($title, '[]');
                        Log::info('Extracted title', ['title' => $title]);
                    }
                    // Extract author - support multiple languages and formats
                    elseif (strpos($line, 'المؤلف:') !== false || strpos($line, 'Author:') !== false) {
                        $author = trim(str_replace(['المؤلف:', 'Author:'], '', $line));
                        // Remove brackets if present
                        $author = trim($author, '[]');
                        Log::info('Extracted author', ['author' => $author]);
                    }
                    // Extract summary - support multiple languages and formats
                    elseif (strpos($line, 'الملخص:') !== false || strpos($line, 'Summary:') !== false) {
                        $summary = trim(str_replace(['الملخص:', 'Summary:'], '', $line));
                        // Remove brackets if present
                        $summary = trim($summary, '[]');
                        Log::info('Extracted summary', ['summary_length' => strlen($summary)]);
                    }
                }
                
                // If AI extraction failed, try manual extraction
                if (empty($title) && empty($author)) {
                    Log::info('AI extraction failed, trying manual extraction');
                    $this->manualExtractBookInfo($processedText, $title, $author, $summary);
                }
                
            } else {
                Log::warning('Gemini API failed, using manual extraction', [
                    'error' => $response['error']
                ]);
                // Fallback to manual extraction
                $this->manualExtractBookInfo($processedText, $title, $author, $summary);
            }
            
        } catch (\Exception $e) {
            Log::error('AI extraction failed, using manual extraction: ' . $e->getMessage());
            $this->manualExtractBookInfo($processedText, $title, $author, $summary);
        }
        
        // Clean and truncate fields to fit database constraints
        $title = !empty($title) ? $this->cleanText(substr($title, 0, 1000)) : '';
        $author = !empty($author) ? $this->cleanText(substr($author, 0, 1000)) : '';
        $summary = !empty($summary) ? $this->cleanText($summary) : ''; // إزالة قطع النص - تنظيف النص فقط دون قطعه
        
        // تنظيف الملخص من عبارات الانقطاع
        if (!empty($summary)) {
            $summary = preg_replace('/\.{3,}$/', '', $summary);
            $summary = preg_replace('/\s*\(بقية الملخص غير متوفرة.*?\)/', '', $summary);
            $summary = preg_replace('/\s*\(.*?يمكن استنتاج.*?\)/', '', $summary);
            $summary = preg_replace('/\s*\(.*?غير متوفرة.*?\)/', '', $summary);
            $summary = trim($summary);
        }
        
        Log::info('Final extracted data', [
            'title' => $title,
            'author' => $author,
            'summary_length' => strlen($summary),
            'has_title' => !empty($title),
            'has_author' => !empty($author)
        ]);
        
        if (!empty($title) || !empty($author)) {
            try {
                // Set default values for required fields based on language
                if ($language === 'English') {
                    $title = !empty($title) ? $title : 'Undefined Title';
                    $author = !empty($author) ? $author : 'Undefined Author';
                } else {
                    $title = !empty($title) ? $title : 'عنوان غير محدد';
                    $author = !empty($author) ? $author : 'مؤلف غير محدد';
                }
                
                Log::info('Creating BookInfo record', [
                    'book_id' => $bookId,
                    'title' => $title,
                    'author' => $author,
                    'language' => $language
                ]);
                
                $bookInfo = BookInfo::create([
                    'book_id' => $bookId,
                    'title' => $title,
                    'author' => $author,
                    'book_summary' => $summary,
                    'language' => $language,
                ]);
                
                Log::info('BookInfo created successfully', [
                    'book_info_id' => $bookInfo->id,
                    'book_id' => $bookInfo->book_id
                ]);
                

                
            } catch (\Exception $e) {
                Log::error('Error creating BookInfo: ' . $e->getMessage(), [
                    'book_id' => $bookId,
                    'title' => $title,
                    'author' => $author,
                    'summary' => $summary,
                    'language' => $language
                ]);
            }
        } else {
            Log::warning('No book info extracted - missing required fields');
        }
        
        Log::info('=== EXTRACT BOOK INFO COMPLETED ===');
    }
    
    /**
     * Manual extraction as fallback
     */
    private function manualExtractBookInfo($text, &$title, &$author, &$summary)
    {
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip very long lines (likely content, not metadata)
            if (strlen($line) > 200) continue;
            
            // Look for title patterns
            if (empty($title) && (
                strpos($line, 'العنوان') !== false || 
                strpos($line, 'Title') !== false ||
                strpos($line, 'اسم الكتاب') !== false ||
                strpos($line, 'Book Title') !== false
            )) {
                $title = trim(str_replace(['العنوان:', 'Title:', 'اسم الكتاب:', 'Book Title:'], '', $line));
            }
            
            // Look for author patterns
            elseif (empty($author) && (
                strpos($line, 'المؤلف') !== false || 
                strpos($line, 'Author') !== false ||
                strpos($line, 'كاتب') !== false ||
                strpos($line, 'Writer') !== false ||
                strpos($line, 'تأليف') !== false
            )) {
                $author = trim(str_replace(['المؤلف:', 'Author:', 'كاتب:', 'Writer:', 'تأليف'], '', $line));
            }
            
            // Look for summary patterns
            elseif (empty($summary) && (
                strpos($line, 'الملخص') !== false || 
                strpos($line, 'Summary') !== false ||
                strpos($line, 'نبذة') !== false ||
                strpos($line, 'Abstract') !== false
            )) {
                $summary = trim(str_replace(['الملخص:', 'Summary:', 'نبذة:', 'Abstract:'], '', $line));
            }
        }
        
        // If still no info found, try to extract from first few meaningful lines
        if (empty($title) && empty($author)) {
            $firstLines = array_slice($lines, 0, 10);
            foreach ($firstLines as $line) {
                $line = trim($line);
                if (empty($line) || strlen($line) > 150) continue;
                
                // If line looks like a title (not too long, not too short)
                if (strlen($line) > 10 && strlen($line) < 100 && empty($title)) {
                    $title = $line;
                }
                // If line contains author-like patterns
                elseif (strpos($line, 'تأليف') !== false || strpos($line, 'by') !== false) {
                    $author = trim(str_replace(['تأليف', 'by'], '', $line));
                }
            }
        }
        
        // Generate summary if not found - تحسين طريقة إنشاء الملخص
        if (empty($summary) && !empty($text)) {
            // أخذ أول 500 حرف كملخص
            $summaryText = substr($text, 0, 500);
            
            // البحث عن نهاية جملة مناسبة
            $lastPeriod = strrpos($summaryText, '.');
            $lastQuestion = strrpos($summaryText, '؟');
            $lastExclamation = strrpos($summaryText, '!');
            
            $endPositions = array_filter([$lastPeriod, $lastQuestion, $lastExclamation]);
            
            if (!empty($endPositions)) {
                $endPos = max($endPositions);
                $summaryText = substr($summaryText, 0, $endPos + 1);
            }
            
            // إزالة أي نص مقطوع أو غير مكتمل
            $summaryText = trim($summaryText);
            
            // إذا كان النص ينتهي بـ "..." أو يحتوي على كلمات تشير للانقطاع، إزالتها
            $summaryText = preg_replace('/\.{3,}$/', '', $summaryText);
            $summaryText = preg_replace('/\s*\(بقية الملخص غير متوفرة.*?\)/', '', $summaryText);
            $summaryText = preg_replace('/\s*\(.*?يمكن استنتاج.*?\)/', '', $summaryText);
            
            $summary = $summaryText;
        }
    }
    
    /**
     * Log processing history
     */
    private function logProcessingHistory($bookId, $originalFile, $processingOptions, $targetLanguage, $status, $errorMessage = null, $processingTime = null)
    {
        // Convert processing options to valid enum values
        $validProcessingTypes = [];
        foreach ($processingOptions as $option) {
            switch ($option) {
                case 'enhance':
                    $validProcessingTypes[] = 'enhance';
                    break;
                case 'translate':
                    $validProcessingTypes[] = 'translate';
                    break;
                case 'summarize':
                    $validProcessingTypes[] = 'summarize';
                    break;

                case 'improve_format':
                case 'improve_formatting': // Fallback for old requests
                    $validProcessingTypes[] = 'improve_format';
                    break;
                case 'extract_info':
                    $validProcessingTypes[] = 'extract_book_info';
                    break;
            }
        }
        
        // Use the first processing type for the enum field
        $processingType = !empty($validProcessingTypes) ? $validProcessingTypes[0] : 'enhance';
        
        ProcessingHistory::create([
            'book_id' => $bookId,
            'original_file' => $originalFile,
            'processing_type' => $processingType,
            'target_language' => $targetLanguage,
            'processing_options' => $processingOptions,
            'processing_status' => $status,
            'error_message' => $errorMessage,
            'processing_time_seconds' => $processingTime ? round($processingTime / 1000, 2) : null,
        ]);
    }
    
    /**
     * Get available processing options
     */
    public function getAvailableProcessingOptions()
    {
        return [
            'extract_info' => __('Book Information Extraction'),
            'summarize' => __('Text Summarization'),
            'translate' => __('Text Translation'),
            'enhance' => __('Text Enhancement'),
            'improve_format' => __('Bullet Points Summary'),
            'blog_article' => __('Professional Blog Article')
        ];
    }
    
    /**
     * Save processed text as a file in processed_texts folder
     */
    private function saveProcessedTextFile($processedText, $bookId, $originalFile, $processType, $targetLanguage)
    {
        Log::info('=== SAVE PROCESSED TEXT FILE START ===', [
            'book_id' => $bookId,
            'original_file' => $originalFile,
            'process_type' => $processType,
            'target_language' => $targetLanguage,
            'processed_text_length' => strlen($processedText)
        ]);
        
        try {
            // Get book identify from book ID
            $book = \App\Models\Book::find($bookId);
            $bookIdentify = $book ? $book->book_identify : "book{$bookId}_" . now()->format('YmdHis');
            
            Log::info('Book identify retrieved', [
                'book_id' => $bookId,
                'book_identify' => $bookIdentify
            ]);
            
            // Create processed_texts directory if it doesn't exist
            $processedTextsPath = storage_path('app/public/processed_texts');
            if (!file_exists($processedTextsPath)) {
                mkdir($processedTextsPath, 0755, true);
                Log::info('Created processed_texts directory', ['path' => $processedTextsPath]);
            }
            
            // Create book folder using book_identify
            $bookFolderPath = $processedTextsPath . '/' . $bookIdentify;
            if (!file_exists($bookFolderPath)) {
                mkdir($bookFolderPath, 0755, true);
                Log::info('Created book folder', ['path' => $bookFolderPath]);
            }
            
            // Create filename
            $timestamp = now()->format('YmdHis');
            $processedFileName = "processed_{$processType}_{$timestamp}.txt";
            $processedFilePath = $bookFolderPath . '/' . $processedFileName;
            
            Log::info('File details', [
                'processedFileName' => $processedFileName,
                'processedFilePath' => $processedFilePath
            ]);
            
            // Save the processed text to file
            $bytesWritten = file_put_contents($processedFilePath, $processedText);
            
            Log::info('File write result', [
                'bytes_written' => $bytesWritten,
                'file_exists' => file_exists($processedFilePath),
                'file_size' => file_exists($processedFilePath) ? filesize($processedFilePath) : 0
            ]);
            
            if ($bytesWritten === false) {
                Log::error('Failed to write processed text file', [
                    'path' => $processedFilePath,
                    'directory_exists' => is_dir($bookFolderPath),
                    'directory_writable' => is_writable($bookFolderPath)
                ]);
                throw new \Exception('Failed to write processed text file');
            }
            
            // Save file record to database
            $this->saveFileRecordToDatabase($processedFileName, $bookId, $processedText, $processType);
            
            Log::info('=== SAVE PROCESSED TEXT FILE COMPLETED ===', [
                'file_path' => $processedFilePath,
                'file_size' => filesize($processedFilePath),
                'book_identify' => $bookIdentify
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error saving processed text file: ' . $e->getMessage(), [
                'book_id' => $bookId,
                'process_type' => $processType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Save file record to database
     */
    private function saveFileRecordToDatabase($fileName, $bookId, $processedText, $processType)
    {
        Log::info('Saving file record to database', [
            'file_name' => $fileName,
            'book_id' => $bookId,
            'process_type' => $processType
        ]);
        
        try {
            // Get book identify from book ID
            $book = \App\Models\Book::find($bookId);
            $bookIdentify = $book ? $book->book_identify : "book{$bookId}_" . now()->format('YmdHis');
            
            $fileSize = strlen($processedText);
            $relativePath = $bookIdentify . '/' . $fileName;
            $fileUrl = url('storage/processed_texts/' . $relativePath);
            
            \App\Models\FileManager::create([
                'name' => $fileName,
                'path' => 'processed_texts/' . $relativePath,
                'size' => $fileSize,
                'type' => 'txt',
                'url' => $fileUrl,
                'folder' => 'processed_texts',
                'modified_at' => now(),
            ]);
            
            Log::info('File record saved to database successfully', [
                'file_name' => $fileName,
                'path' => 'processed_texts/' . $relativePath,
                'size' => $fileSize,
                'book_identify' => $bookIdentify
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to save file record to database', [
                'error' => $e->getMessage(),
                'file_name' => $fileName,
                'book_id' => $bookId
            ]);
            throw $e;
        }
    }
    
    /**
     * Clean text for database storage
     */
    private function cleanText($text)
    {
        if (empty($text)) {
            return '';
        }
        
        // Remove any BOM characters
        $text = str_replace("\xEF\xBB\xBF", '', $text);
        
        // Remove only the most problematic control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Remove any truncated characters at the end - تحسين لتجنب قطع النص المفيد
        $text = preg_replace('/\?{3,}$/', '', $text); // إزالة علامات استفهام متكررة فقط
        
        // Ensure proper UTF-8 encoding without being too aggressive
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // Trim whitespace
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Ensure BookInfo record exists for the book
     */
    private function ensureBookInfoExists($bookId, $originalFile, $processedText, $targetLanguage)
    {
        try {
            // تحقق من وجود سجل في books_info
            $bookInfo = \App\Models\BookInfo::where('book_id', $bookId)->first();
            
            if (!$bookInfo) {
                Log::info('No BookInfo found, creating new record', [
                    'book_id' => $bookId,
                    'original_file' => $originalFile
                ]);
                
                // استخراج عنوان من اسم الملف
                $title = $this->extractTitleFromFileName($originalFile);
                
                // إنشاء سجل في books_info
                \App\Models\BookInfo::create([
                    'book_id' => $bookId,
                    'title' => $title,
                    'author' => 'غير محدد',
                    'book_summary' => $processedText, // استخدام النص الكامل بدلاً من قطعه
                    'language' => $targetLanguage,
                ]);
                
                Log::info('BookInfo created automatically', [
                    'book_id' => $bookId,
                    'title' => $title,
                    'language' => $targetLanguage
                ]);
            } else {
                Log::info('BookInfo already exists', [
                    'book_id' => $bookId,
                    'book_info_id' => $bookInfo->id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error ensuring BookInfo exists: ' . $e->getMessage(), [
                'book_id' => $bookId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Extract title from file name
     */
    private function extractTitleFromFileName($fileName)
    {
        // إزالة امتداد الملف
        $nameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        
        // إزالة الأرقام والتواريخ من نهاية الاسم
        $cleanName = preg_replace('/[-_]\d{8,}$/', '', $nameWithoutExtension);
        
        // تنظيف الاسم من الرموز الخاصة
        $cleanName = preg_replace('/[_-]/', ' ', $cleanName);
        
        // تحويل أول حرف إلى كبير
        $cleanName = ucfirst(trim($cleanName));
        
        return $cleanName ?: 'كتاب بدون عنوان';
    }
    
    /**
     * Extract SEO keywords from article content
     */
    private function extractSeoKeywords(string $content): string
    {
        // Clean content and extract words
        $cleanContent = strip_tags($content);
        $words = str_word_count(strtolower($cleanContent), 1);
        $wordCount = array_count_values($words);
        
        // Remove common words in multiple languages
        $commonWords = [
            // English common words
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'a', 'an', 'as', 'so', 'if', 'then', 'else', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'than', 'too', 'very', 'you', 'your', 'yours', 'yourself', 'yourselves', 'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'what', 'which', 'who', 'whom', 'whose', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'would', 'should', 'could', 'ought', 'im', 'youre', 'hes', 'shes', 'its', 'were', 'theyre', 'ive', 'youve', 'weve', 'theyve', 'id', 'youd', 'hed', 'shed', 'wed', 'theyd', 'ill', 'youll', 'hell', 'shell', 'well', 'theyll', 'isnt', 'arent', 'wasnt', 'werent', 'hasnt', 'havent', 'hadnt', 'doesnt', 'dont', 'didnt', 'wont', 'wouldnt', 'couldnt', 'shouldnt', 'let', 'lets', 'thats', 'whos', 'whats', 'heres', 'theres', 'whens', 'wheres', 'whys', 'hows',
            
            // Arabic common words
            'في', 'من', 'إلى', 'على', 'هذا', 'هذه', 'ذلك', 'تلك', 'التي', 'الذي', 'الذين', 'اللاتي', 'اللائي', 'هو', 'هي', 'هم', 'هن', 'أنا', 'نحن', 'أنت', 'أنتما', 'أنتم', 'أنتن', 'هو', 'هي', 'هما', 'هم', 'هن', 'كان', 'كانت', 'كانوا', 'كن', 'يكون', 'تكون', 'يكونون', 'تكونون', 'أكون', 'نكون', 'يكون', 'تكون', 'يكونون', 'تكونون', 'أكون', 'نكون', 'له', 'لها', 'لهما', 'لهم', 'لهن', 'لي', 'لنا', 'لك', 'لكما', 'لكم', 'لكن', 'له', 'لها', 'لهما', 'لهم', 'لهن', 'لي', 'لنا', 'لك', 'لكما', 'لكم', 'لكن',
            
            // French common words
            'le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'et', 'ou', 'mais', 'dans', 'sur', 'avec', 'sans', 'pour', 'par', 'contre', 'chez', 'chez', 'sous', 'entre', 'devant', 'derrière', 'avant', 'après', 'pendant', 'depuis', 'jusqu', 'vers', 'vers', 'selon', 'selon', 'd', 'après', 'avant', 'pendant', 'depuis', 'jusqu', 'vers', 'selon', 'd', 'après', 'avant', 'pendant', 'depuis', 'jusqu', 'vers', 'selon',
            
            // Spanish common words
            'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'y', 'o', 'pero', 'en', 'sobre', 'con', 'sin', 'para', 'por', 'contra', 'entre', 'después', 'antes', 'durante', 'desde', 'hasta', 'hacia', 'según', 'según', 'de', 'del', 'al', 'a', 'ante', 'bajo', 'cabe', 'con', 'contra', 'de', 'desde', 'durante', 'en', 'entre', 'hacia', 'hasta', 'mediante', 'para', 'por', 'según', 'sin', 'so', 'sobre', 'tras',
            
            // German common words
            'der', 'die', 'das', 'den', 'dem', 'des', 'ein', 'eine', 'eines', 'einer', 'einem', 'einen', 'und', 'oder', 'aber', 'in', 'auf', 'mit', 'ohne', 'für', 'gegen', 'zwischen', 'vor', 'nach', 'während', 'seit', 'bis', 'zu', 'nach', 'von', 'aus', 'bei', 'seit', 'trotz', 'wegen', 'dank', 'an', 'auf', 'hinter', 'in', 'neben', 'über', 'unter', 'vor', 'zwischen',
            
            // Single letters and numbers
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];
        
        // Remove common words
        foreach ($commonWords as $word) {
            unset($wordCount[$word]);
        }
        
        // Filter out words that are too short (less than 3 characters)
        $filteredWords = [];
        foreach ($wordCount as $word => $count) {
            if (strlen($word) >= 3 && $count >= 2) { // Only words that appear at least twice
                $filteredWords[$word] = $count;
            }
        }
        
        // Sort by frequency and get top 15 keywords
        arsort($filteredWords);
        $keywords = array_slice(array_keys($filteredWords), 0, 15);
        
        // If we don't have enough keywords, add some from the original list
        if (count($keywords) < 5) {
            $additionalKeywords = array_slice(array_keys($wordCount), 0, 10);
            $keywords = array_merge($keywords, $additionalKeywords);
            $keywords = array_unique($keywords);
        }
        
        return implode(', ', $keywords);
    }

    /**
     * Get available languages
     */
    public function getAvailableLanguages()
    {
        $languages = [
            'English', 'Arabic', 'French', 'Spanish', 'German', 'Italian',
            'Portuguese', 'Russian', 'Chinese', 'Japanese', 'Korean', 'Turkish',
            'Persian', 'Urdu', 'Hindi', 'Bengali'
        ];
        
        $result = [];
        foreach ($languages as $language) {
            $result[$language] = LanguageHelper::arabizeLanguageName($language);
        }
        
        return $result;
    }
}