<?php

namespace App\Http\Controllers;

use App\Services\AiProcessorService;
use App\Services\FileManagerService;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AiProcessorController extends Controller
{
    private $aiProcessorService;
    private $fileManagerService;
    
    public function __construct(AiProcessorService $aiProcessorService, FileManagerService $fileManagerService)
    {
        $this->aiProcessorService = $aiProcessorService;
        $this->fileManagerService = $fileManagerService;
    }
    
    /**
     * Show AI processor interface
     */
    public function show($bookId)
    {
        // Get book files
        $files = $this->fileManagerService->getBookFiles($bookId);
        
        // Get book information from books_info table
        $bookInfo = \App\Models\BookInfo::join('books', 'books_info.book_id', '=', 'books.id')
            ->where('books.book_identify', $bookId)
            ->select('books_info.title', 'books_info.author', 'books_info.language')
            ->first();
        
        // Get processing statistics
        $processingStats = $this->getProcessingStatistics($bookId);
        
        // Debug: Log the statistics
        \Log::info('Processing Statistics for book: ' . $bookId, [
            'summarized_count' => $processingStats['summarized']->count(),
            'formatting_count' => $processingStats['formatting']->count(),
            'translated_count' => $processingStats['translated']->count(),
            'enhanced_count' => $processingStats['enhanced']->count(),
            'summarized_data' => $processingStats['summarized']->toArray(),
            'formatting_data' => $processingStats['formatting']->toArray(),
            'translated_data' => $processingStats['translated']->toArray(),
            'enhanced_data' => $processingStats['enhanced']->toArray(),
        ]);
        
        // Get available options
        $processingOptions = $this->aiProcessorService->getAvailableProcessingOptions();
        $availableLanguages = $this->aiProcessorService->getAvailableLanguages();
        
        return view('ai-processor.show', compact('bookId', 'files', 'processingOptions', 'availableLanguages', 'bookInfo', 'processingStats'));
    }
    
    /**
     * Get processing statistics for the book
     */
    private function getProcessingStatistics($bookId)
    {
        // Get book ID from book_identify
        $book = \App\Models\Book::where('book_identify', $bookId)->first();
        if (!$book) {
            return [
                'summarized' => collect(),
                'formatting' => collect(),
                'translated' => collect(),
                'enhanced' => collect(),
            ];
        }
        
        // Get statistics for each processing type and language
        $summarizedStats = \App\Models\SummarizedText::where('book_id', $book->id)
            ->selectRaw('target_language, COUNT(*) as count')
            ->groupBy('target_language')
            ->get();
            
        $formattingStats = \App\Models\FormattingImprovedText::where('book_id', $book->id)
            ->selectRaw('target_language, COUNT(*) as count')
            ->groupBy('target_language')
            ->get();
            
        $translatedStats = \App\Models\TranslatedText::where('book_id', $book->id)
            ->selectRaw('target_language, COUNT(*) as count')
            ->groupBy('target_language')
            ->get();
            
        $enhancedStats = \App\Models\EnhancedText::where('book_id', $book->id)
            ->selectRaw('target_language, COUNT(*) as count')
            ->groupBy('target_language')
            ->get();
        
        return [
            'summarized' => $summarizedStats,
            'formatting' => $formattingStats,
            'translated' => $translatedStats,
            'enhanced' => $enhancedStats,
        ];
    }
    
    /**
     * Process files with AI
     */
    public function processFiles(Request $request)
    {
        Log::info('=== AI PROCESSOR CONTROLLER START ===', [
            'request_data' => $request->all()
        ]);
        
        $request->validate([
            'book_id' => 'required|string',
            'selected_files' => 'required|array|min:1',
            'processing_options' => 'required|array|min:1',
            'target_language' => 'required|string'
        ]);
        
        $results = [];
        $bookId = $request->book_id;
        
        Log::info('Validation passed', [
            'book_id' => $bookId,
            'selected_files_count' => count($request->selected_files),
            'processing_options' => $request->processing_options,
            'target_language' => $request->target_language
        ]);
        
        foreach ($request->selected_files as $filename) {
                            Log::info('Processing file', [
                    'filename' => $filename,
                    'book_identify' => $bookId
                ]);
            
            try {
                // Get file content
                $fileContent = $this->getFileContent($bookId, $filename); // $bookId is actually book_identify
                
                if ($fileContent === false) {
                    Log::error('File content not found', [
                        'filename' => $filename,
                        'book_identify' => $bookId
                    ]);
                    $results[] = [
                        'filename' => $filename,
                        'success' => false,
                        'error' => 'لا يمكن قراءة الملف'
                    ];
                    continue;
                }
                
                Log::info('File content loaded', [
                    'filename' => $filename,
                    'content_length' => strlen($fileContent)
                ]);
                
                // Process with AI
                Log::info('Calling AI processor service', [
                    'filename' => $filename,
                    'book_identify' => $bookId,
                    'processing_options' => $request->processing_options,
                    'target_language' => $request->target_language
                ]);
                
                $result = $this->aiProcessorService->processText(
                    $fileContent,
                    $request->processing_options,
                    $request->target_language,
                    $bookId, // This is actually book_identify
                    $filename
                );
                
                Log::info('AI processing result', [
                    'filename' => $filename,
                    'success' => $result['success'],
                    'has_text' => isset($result['text']),
                    'has_error' => isset($result['error']),
                    'processing_time' => $result['processing_time'] ?? null
                ]);
                
                $results[] = [
                    'filename' => $filename,
                    'success' => $result['success'],
                    'text' => $result['text'] ?? null,
                    'error' => $result['error'] ?? null,
                    'processing_time' => $result['processing_time'] ?? null
                ];
                
            } catch (\Exception $e) {
                Log::error('AI Processing Error for file ' . $filename . ': ' . $e->getMessage());
                
                $results[] = [
                    'filename' => $filename,
                    'success' => false,
                    'error' => 'خطأ في معالجة الملف: ' . $e->getMessage()
                ];
            }
        }
        
        Log::info('=== AI PROCESSOR CONTROLLER COMPLETED ===', [
            'total_files' => count($request->selected_files),
            'successful_results' => count(array_filter($results, fn($r) => $r['success'])),
            'failed_results' => count(array_filter($results, fn($r) => !$r['success']))
        ]);
        
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
    
    /**
     * Get file content
     */
    private function getFileContent($bookIdentify, $filename)
    {
        Log::info('Getting file content', [
            'book_identify' => $bookIdentify,
            'filename' => $filename
        ]);
        
        try {
            // Try to find the file in extracted_texts folder
            $filePath = storage_path('app/public/extracted_texts/' . $bookIdentify . '/' . $filename);
            
            Log::info('Checking extracted_texts path', [
                'path' => $filePath,
                'exists' => file_exists($filePath)
            ]);
            
            if (!file_exists($filePath)) {
                // Try processed_texts folder
                $filePath = storage_path('app/public/processed_texts/' . $bookIdentify . '/' . $filename);
                
                Log::info('Checking processed_texts path', [
                    'path' => $filePath,
                    'exists' => file_exists($filePath)
                ]);
            }
            
            if (!file_exists($filePath)) {
                Log::error('File not found in any location', [
                    'book_identify' => $bookIdentify,
                    'filename' => $filename,
                    'extracted_path' => storage_path('app/public/extracted_texts/' . $bookIdentify . '/' . $filename),
                    'processed_path' => storage_path('app/public/processed_texts/' . $bookIdentify . '/' . $filename)
                ]);
                return false;
            }
            
            $content = file_get_contents($filePath);
            Log::info('File content loaded successfully', [
                'path' => $filePath,
                'content_length' => strlen($content)
            ]);
            
            return $content;
            
        } catch (\Exception $e) {
            Log::error('Error reading file: ' . $e->getMessage(), [
                'book_identify' => $bookIdentify,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get processing history
     */
    public function getProcessingHistory($bookIdentify)
    {
        // Get actual book ID from book_identify
        $book = \App\Models\Book::where('book_identify', $bookIdentify)->first();
        if (!$book) {
            return response()->json([
                'success' => true,
                'history' => []
            ]);
        }
        
        $limit = request('limit', 10); // Default to 10 records
        $offset = request('offset', 0);
        
        $history = \App\Models\ProcessingHistory::where('book_id', $book->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
            
        $totalCount = \App\Models\ProcessingHistory::where('book_id', $book->id)->count();
            
        return response()->json([
            'success' => true,
            'history' => $history,
            'total_count' => $totalCount,
            'has_more' => ($offset + $limit) < $totalCount
        ]);
    }
    
    /**
     * Get processed texts
     */
    public function getProcessedTexts($bookIdentify)
    {
        // Get actual book ID from book_identify
        $book = \App\Models\Book::where('book_identify', $bookIdentify)->first();
        if (!$book) {
            return response()->json([
                'success' => true,
                'enhanced_texts' => [],
                'translated_texts' => [],
                'summarized_texts' => [],
                'language_improved_texts' => [],
                'formatting_improved_texts' => [],
            ]);
        }
        
        $enhancedTexts = \App\Models\EnhancedText::where('book_id', $book->id)->get();
        $translatedTexts = \App\Models\TranslatedText::where('book_id', $book->id)->get();
        $summarizedTexts = \App\Models\SummarizedText::where('book_id', $book->id)->get();
        $languageImprovedTexts = \App\Models\LanguageImprovedText::where('book_id', $book->id)->get();
        $formattingImprovedTexts = \App\Models\FormattingImprovedText::where('book_id', $book->id)->get();
        
        return response()->json([
            'success' => true,
            'enhanced_texts' => $enhancedTexts,
            'translated_texts' => $translatedTexts,
            'summarized_texts' => $summarizedTexts,
            'language_improved_texts' => $languageImprovedTexts,
            'formatting_improved_texts' => $formattingImprovedTexts,
        ]);
    }
} 