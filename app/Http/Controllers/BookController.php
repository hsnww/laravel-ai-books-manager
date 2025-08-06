<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookInfo;
use App\Models\EnhancedText;
use App\Models\TranslatedText;
use App\Models\SummarizedText;
use App\Models\FormattingImprovedText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class BookController extends Controller
{
    /**
     * Show book details page
     */
    public function show(Request $request, $bookIdentify)
    {
        // Set language based on request or browser
        $locale = $request->get('lang', $request->getLocale());
        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
        }

        // Get book with all related data
        $book = Book::with(['bookInfo', 'user'])->where('book_identify', $bookIdentify)->firstOrFail();

        // Get available languages for this book
        $availableLanguages = $this->getAvailableLanguages($book->id);

        // Get selected language from request or use first available language
        $selectedLanguage = $request->get('language', $availableLanguages->first() ?? 'Arabic');

        // Get processing statistics for selected language
        $processingStats = $this->getProcessingStats($book->id, $selectedLanguage);

        // Get first text of each type for the selected language
        $firstTexts = $this->getFirstTexts($book->id, $selectedLanguage);

        // Helper function to check if language is RTL
        $isRtlLanguage = function($language) {
            $rtlLanguages = [
                'arabic', 'ar', 'عربي', 'العربية', 'arabic', 'arabic',
                'hebrew', 'he', 'עברית',
                'persian', 'fa', 'فارسی',
                'urdu', 'ur', 'اردو',
                'sindhi', 'sd', 'سنڌي',
                'kashmiri', 'ks', 'کٲشُر',
                'pashto', 'ps', 'پښتو',
                'dari', 'prs', 'دری'
            ];
            return in_array(strtolower(trim($language)), $rtlLanguages);
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

        $languages = $languages->merge($summarizedLanguages)
            ->merge($formattingLanguages)
            ->merge($translatedLanguages)
            ->merge($enhancedLanguages)
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
            $stats = $this->getProcessingStats($book->id, $language);

            return response()->json([
                'success' => true,
                'stats' => $stats
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
                }

                return [
                    'id' => $text->id,
                    'title' => $text->title,
                    'content' => $content,
                    'created_at' => $text->created_at->format('Y-m-d H:i:s'),
                    'total_count' => $texts->count()
                ];
            });

            return response()->json([
                'success' => true,
                'texts' => $formattedTexts,
                'total_count' => $texts->count()
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
                default:
                    $textContent = $text->processed_text;
                    break;
            }

            return response()->json([
                'success' => true,
                'text' => $textContent,
                'title' => $text->title,
                'language' => $text->target_language,
                'type' => $type,
                'created_at' => $text->created_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getProcessedText: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
} 