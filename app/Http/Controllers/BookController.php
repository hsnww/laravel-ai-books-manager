<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookInfo;
use App\Models\EnhancedText;
use App\Models\TranslatedText;
use App\Models\SummarizedText;
use App\Models\FormattingImprovedText;
use App\Models\BlogArticle;
use App\Services\BookInfoService;
use App\Helpers\LanguageHelper;
use Illuminate\Http\Request;

class BookController extends Controller
{
    protected $bookInfoService;

    public function __construct(BookInfoService $bookInfoService)
    {
        $this->bookInfoService = $bookInfoService;
    }

    /**
     * Show book details page
     */
    public function show(Request $request, $bookIdentify)
    {
        // Set language based on request or browser
        $locale = $request->get('lang', $request->getLocale());
        if (LanguageHelper::isValidLanguage($locale)) {
            LanguageHelper::setLanguage($locale);
        }

        // Get book with all related data
        $book = Book::with(['bookInfos', 'user'])->where('book_identify', $bookIdentify)->firstOrFail();

        // Get available languages for this book
        $availableLanguages = $this->getAvailableLanguages($book->id);

        // Get selected language from request or use first available language
        $selectedLanguage = $request->get('language', $availableLanguages->first() ?? 'Arabic');
        
        // Normalize language values to match what's stored in database
        $selectedLanguage = LanguageHelper::normalizeLanguageName($selectedLanguage);

        // Get preferred book info using BookInfoService
        $preferredBookInfo = $this->bookInfoService->getBookWithPreferredInfo($book, $selectedLanguage);

        // Get processing statistics for selected language
        $processingStats = $this->getProcessingStats($book->id, $selectedLanguage);

        // Get first text of each type for the selected language
        $firstTexts = $this->getFirstTexts($book->id, $selectedLanguage);

        // Helper function to check if language is RTL
        $isRtlLanguage = function($language) {
            return LanguageHelper::isRtlLanguage($language);
        };

        // Helper function to detect Arabic text content
        $detectArabicText = function($text) {
            return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
        };

        return view('books.show', compact(
            'book', 
            'processingStats',
            'firstTexts',
            'selectedLanguage',
            'availableLanguages',
            'preferredBookInfo',
            'isRtlLanguage',
            'detectArabicText'
        ));
    }

    /**
     * Get processing statistics for a specific language
     */
    private function getProcessingStats($bookId, $language)
    {
        return [
            'summarized' => SummarizedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->count(),
            'formatting' => FormattingImprovedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->count(),
            'translated' => TranslatedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->count(),
            'enhanced' => EnhancedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->count(),
            'blog_articles' => BlogArticle::where('book_id', $bookId)
                ->where('target_language', $language)
                ->count(),
        ];
    }

    /**
     * Get first text of each type for selected language
     */
    private function getFirstTexts($bookId, $language)
    {
        return [
            'summarized' => SummarizedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->orderBy('created_at', 'asc')
                ->first(),
            'formatting' => FormattingImprovedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->orderBy('created_at', 'asc')
                ->first(),
            'translated' => TranslatedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->orderBy('created_at', 'asc')
                ->first(),
            'enhanced' => EnhancedText::where('book_id', $bookId)
                ->where('target_language', $language)
                ->orderBy('created_at', 'asc')
                ->first(),
            'blog_articles' => BlogArticle::where('book_id', $bookId)
                ->where('target_language', $language)
                ->orderBy('created_at', 'asc')
                ->first(),
        ];
    }

    /**
     * Get available languages for this book
     */
    private function getAvailableLanguages($bookId)
    {
        $languages = collect();

        // Get languages from all text types
        $summarizedLanguages = SummarizedText::where('book_id', $bookId)->pluck('target_language')->unique();
        $formattingLanguages = FormattingImprovedText::where('book_id', $bookId)->pluck('target_language')->unique();
        $translatedLanguages = TranslatedText::where('book_id', $bookId)->pluck('target_language')->unique();
        $enhancedLanguages = EnhancedText::where('book_id', $bookId)->pluck('target_language')->unique();
        $blogArticleLanguages = BlogArticle::where('book_id', $bookId)->pluck('target_language')->unique();

        $languages = $languages->merge($summarizedLanguages)
            ->merge($formattingLanguages)
            ->merge($translatedLanguages)
            ->merge($enhancedLanguages)
            ->merge($blogArticleLanguages)
            ->unique()
            ->values();

        return $languages;
    }

    /**
     * Get processing statistics for a specific language
     */
    public function getProcessingStatsByLanguage(Request $request, $bookIdentify, $language)
    {
        try {
            $book = Book::where('book_identify', $bookIdentify)->firstOrFail();
            
            // Normalize language values to match what's stored in database
            $language = LanguageHelper::normalizeLanguageName($language);
            
            $stats = $this->getProcessingStats($book->id, $language);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'language' => $language,
                'language_display' => LanguageHelper::getLocalizedLanguageName($language),
                'is_rtl' => LanguageHelper::isRtlLanguage($language)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting processing stats: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get texts by type and language for pagination
     */
    public function getTextsByType(Request $request, $bookIdentify, $type, $language)
    {
        try {
            $book = Book::where('book_identify', $bookIdentify)->firstOrFail();
            
            // Normalize language values to match what's stored in database
            $language = LanguageHelper::normalizeLanguageName($language);

            $texts = collect();
            switch ($type) {
                case 'summarized':
                    $texts = SummarizedText::where('book_id', $book->id)
                        ->where('target_language', $language)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
                case 'formatting':
                    $texts = FormattingImprovedText::where('book_id', $book->id)
                        ->where('target_language', $language)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
                case 'translated':
                    $texts = TranslatedText::where('book_id', $book->id)
                        ->where('target_language', $language)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
                case 'enhanced':
                    $texts = EnhancedText::where('book_id', $book->id)
                        ->where('target_language', $language)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
                case 'blog_articles':
                    $texts = BlogArticle::where('book_id', $book->id)
                        ->where('target_language', $language)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            $formattedTexts = $texts->map(function($text) use ($type, $texts) {
                $content = '';
                switch ($type) {
                    case 'summarized':
                        $content = $text->summarized_text;
                        break;
                    case 'formatting':
                        $content = $text->improved_text;
                        break;
                    case 'translated':
                        $content = $text->translated_text;
                        break;
                    case 'enhanced':
                        $content = $text->enhanced_text;
                        break;
                    case 'blog_articles':
                        $content = $text->article_content;
                        break;
                }

                return [
                    'id' => $text->id,
                    'title' => $text->title,
                    'content' => $content,
                    'language' => $text->target_language,
                    'language_display' => LanguageHelper::getLocalizedLanguageName($text->target_language),
                    'is_rtl' => LanguageHelper::isRtlLanguage($text->target_language),
                    'created_at' => $text->created_at->format('Y-m-d H:i:s'),
                    'total_count' => $texts->count()
                ];
            });

            return response()->json([
                'success' => true,
                'texts' => $formattedTexts,
                'total_count' => $texts->count(),
                'language' => $language,
                'language_display' => LanguageHelper::getLocalizedLanguageName($language),
                'is_rtl' => LanguageHelper::isRtlLanguage($language)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getTextsByType: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get processed text content
     */
    public function getProcessedText(Request $request, $bookIdentify, $textId, $type)
    {
        try {
            $book = Book::where('book_identify', $bookIdentify)->firstOrFail();
            
            $text = null;
            switch ($type) {
                case 'enhanced':
                    $text = EnhancedText::where('id', $textId)->where('book_id', $book->id)->first();
                    break;
                case 'translated':
                    $text = TranslatedText::where('id', $textId)->where('book_id', $book->id)->first();
                    break;
                case 'summarized':
                    $text = SummarizedText::where('id', $textId)->where('book_id', $book->id)->first();
                    break;
                case 'formatting':
                    $text = FormattingImprovedText::where('id', $textId)->where('book_id', $book->id)->first();
                    break;
                case 'blog_articles':
                    $text = BlogArticle::where('id', $textId)->where('book_id', $book->id)->first();
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            if (!$text) {
                return response()->json(['error' => 'Text not found'], 404);
            }

            $textContent = '';
            switch ($type) {
                case 'summarized':
                    $textContent = $text->summarized_text;
                    break;
                case 'translated':
                    $textContent = $text->translated_text;
                    break;
                case 'enhanced':
                    $textContent = $text->enhanced_text;
                    break;
                case 'formatting':
                    $textContent = $text->improved_text;
                    break;
                case 'blog_articles':
                    $textContent = $text->article_content;
                    break;
                default:
                    $textContent = $text->processed_text;
                    break;
            }

            return response()->json([
                'success' => true,
                'text' => $textContent,
                'title' => $text->title,
                'language' => $text->target_language,
                'language_display' => LanguageHelper::getLocalizedLanguageName($text->target_language),
                'type' => $type,
                'created_at' => $text->created_at->format('Y-m-d H:i:s'),
                'is_rtl' => LanguageHelper::isRtlLanguage($text->target_language)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getProcessedText: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get book information by language
     */
    public function getBookInfoByLanguage(Request $request, $bookIdentify, $language)
    {
        try {
            \Log::info("BookController: Requesting book info for book '{$bookIdentify}' with language '{$language}'");
            
            $book = Book::where('book_identify', $bookIdentify)->firstOrFail();
            
            // Normalize language values to match what's stored in database with fallback
            $originalLanguage = $language;
            $language = LanguageHelper::normalizeLanguageNameWithFallback($language, 'English');
            
            if ($originalLanguage !== $language) {
                \Log::info("BookController: Language normalized from '{$originalLanguage}' to '{$language}'");
            }

            // Get book info for the specified language
            $bookInfo = $this->bookInfoService->getBookInfoByLanguage($book, $language);

            if ($bookInfo) {
                \Log::info("BookController: Found book info for language '{$language}'");
                return response()->json([
                    'success' => true,
                    'bookInfo' => [
                        'title' => $bookInfo->title,
                        'author' => $bookInfo->author,
                        'book_summary' => $bookInfo->book_summary,
                        'language' => $bookInfo->language,
                        'language_display' => LanguageHelper::getLocalizedLanguageName($bookInfo->language),
                        'is_rtl' => LanguageHelper::isRtlLanguage($bookInfo->language)
                    ]
                ]);
            } else {
                \Log::warning("BookController: No book info found for language '{$language}', trying fallback");
                
                // Try to get book info with fallback language
                $fallbackLanguage = 'English';
                $fallbackBookInfo = $this->bookInfoService->getBookInfoByLanguage($book, $fallbackLanguage);
                
                if ($fallbackBookInfo) {
                    \Log::info("BookController: Using fallback language '{$fallbackLanguage}'");
                    return response()->json([
                        'success' => true,
                        'bookInfo' => [
                            'title' => $fallbackBookInfo->title,
                            'author' => $fallbackBookInfo->author,
                            'book_summary' => $fallbackBookInfo->book_summary,
                            'language' => $fallbackBookInfo->language,
                            'language_display' => LanguageHelper::getLocalizedLanguageName($fallbackBookInfo->language),
                            'is_rtl' => LanguageHelper::isRtlLanguage($fallbackBookInfo->language),
                            'fallback_used' => true,
                            'original_language' => $originalLanguage
                        ]
                    ]);
                } else {
                    \Log::warning("BookController: No book info found even with fallback language");
                    return response()->json([
                        'success' => false,
                        'message' => 'No book information available for the specified language or fallback language',
                        'requested_language' => $originalLanguage,
                        'normalized_language' => $language,
                        'fallback_language' => $fallbackLanguage
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting book info by language: ' . $e->getMessage(), [
                'book_identify' => $bookIdentify,
                'language' => $language ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
} 