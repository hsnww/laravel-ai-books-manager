<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookInfo;
use App\Helpers\LanguageHelper;
use Illuminate\Support\Collection;

class BookInfoService
{
    /**
     * الحصول على معلومات الكتاب حسب اللغة
     */
    public function getBookInfoByLanguage(Book $book, $language = null)
    {
        \Log::info("BookInfoService: Getting book info for book ID {$book->id} with language: " . ($language ?? 'null'));
        
        if (!$language) {
            // إذا لم يتم تحديد اللغة، استخدم لغة المتصفح أو اللغة الافتراضية
            $language = LanguageHelper::getPreferredLanguage();
            \Log::info("BookInfoService: No language specified, using preferred language: {$language}");
        }

        // التحقق من صحة اللغة
        if (!LanguageHelper::isValidLanguage($language)) {
            $originalLanguage = $language;
            $language = LanguageHelper::getPreferredLanguage();
            \Log::warning("BookInfoService: Invalid language '{$originalLanguage}', using preferred language: {$language}");
        }

        // البحث عن معلومات الكتاب باللغة المحددة
        $bookInfo = $book->bookInfos()
            ->where('language', $language)
            ->first();

        if ($bookInfo) {
            \Log::info("BookInfoService: Found book info for language '{$language}'");
            return $bookInfo;
        }

        \Log::warning("BookInfoService: No book info found for language '{$language}', trying fallback languages");

        // إذا لم يتم العثور على معلومات باللغة المحددة، ابحث عن اللغة الافتراضية
        $defaultLanguage = LanguageHelper::getPreferredLanguage();
        if ($defaultLanguage !== $language) {
            $defaultBookInfo = $book->bookInfos()
                ->where('language', $defaultLanguage)
                ->first();

            if ($defaultBookInfo) {
                \Log::info("BookInfoService: Using default language '{$defaultLanguage}' as fallback");
                return $defaultBookInfo;
            }
        }

        // إذا لم يتم العثور على أي معلومات، ابحث عن اللغة الإنجليزية
        $englishBookInfo = $book->bookInfos()
            ->where('language', 'English')
            ->first();

        if ($englishBookInfo) {
            \Log::info("BookInfoService: Using English as fallback language");
            return $englishBookInfo;
        }

        // إذا لم يتم العثور على أي معلومات، ارجع أول مدخل متوفر
        $firstBookInfo = $book->bookInfos()->first();
        if ($firstBookInfo) {
            \Log::info("BookInfoService: Using first available book info with language '{$firstBookInfo->language}'");
        } else {
            \Log::warning("BookInfoService: No book info found at all for book ID {$book->id}");
        }
        
        return $firstBookInfo;
    }

    /**
     * الحصول على جميع اللغات المتوفرة لمعلومات الكتاب
     */
    public function getAvailableLanguages(Book $book): Collection
    {
        return $book->bookInfos()
            ->pluck('language')
            ->unique()
            ->values();
    }

    /**
     * الحصول على معلومات الكتب لصفحة القائمة مع اللغة المفضلة
     */
    public function getBooksWithPreferredInfo($books)
    {
        return $books->map(function ($book) {
            $preferredLanguage = LanguageHelper::getPreferredLanguage();
            
            // البحث عن معلومات الكتاب باللغة المفضلة
            $bookInfo = $book->bookInfos->where('language', $preferredLanguage)->first();
            
            // إذا لم يتم العثور على معلومات باللغة المفضلة، استخدم أول مدخل متوفر
            if (!$bookInfo) {
                $bookInfo = $book->bookInfos->first();
            }
            
            $book->preferred_book_info = $bookInfo;
            return $book;
        });
    }

    /**
     * الحصول على معلومات الكتاب مع أفضلية للغة المتصفح
     */
    public function getBookWithPreferredInfo(Book $book, $requestedLanguage = null)
    {
        // إذا تم تحديد لغة محددة، استخدمها
        if ($requestedLanguage) {
            $bookInfo = $this->getBookInfoByLanguage($book, $requestedLanguage);
            if ($bookInfo) {
                return $bookInfo;
            }
        }

        // استخدم لغة المتصفح
        $preferredLanguage = LanguageHelper::getPreferredLanguage();
        
        // البحث عن معلومات الكتاب باللغة المفضلة
        $bookInfo = $book->bookInfos->where('language', $preferredLanguage)->first();
        
        if ($bookInfo) {
            return $bookInfo;
        }

        // إذا لم يتم العثور على معلومات باللغة المفضلة، ابحث عن اللغة الافتراضية
        $currentLocale = LanguageHelper::getCurrentLanguage();
        $defaultLanguage = $currentLocale === 'ar' ? 'Arabic' : 'English';
        $defaultBookInfo = $book->bookInfos->where('language', $defaultLanguage)->first();
        
        if ($defaultBookInfo) {
            return $defaultBookInfo;
        }

        // إذا لم يتم العثور على أي معلومات، ارجع أول مدخل متوفر
        return $book->bookInfos()->first();
    }

    /**
     * إنشاء أو تحديث معلومات الكتاب
     */
    public function createOrUpdateBookInfo($bookId, $data)
    {
        return BookInfo::updateOrCreate(
            [
                'book_id' => $bookId,
                'language' => $data['language']
            ],
            [
                'title' => $data['title'],
                'author' => $data['author'],
                'book_summary' => $data['book_summary'] ?? null,
            ]
        );
    }

    /**
     * حذف معلومات الكتاب حسب اللغة
     */
    public function deleteBookInfoByLanguage($bookId, $language)
    {
        return BookInfo::where('book_id', $bookId)
            ->where('language', $language)
            ->delete();
    }

    /**
     * الحصول على إحصائيات معلومات الكتب حسب اللغة
     */
    public function getBookInfoStats()
    {
        return BookInfo::selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->orderBy('count', 'desc')
            ->get();
    }
}
