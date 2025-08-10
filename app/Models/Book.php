<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\LanguageHelper;

class Book extends Model
{
    use HasFactory;
    
    protected $table = 'books';
    
    protected $fillable = [
        'book_identify',
        'user_id',
        'book_language',
        'file_path',
        'file_size',
        'pages_count',
        'extraction_time'
    ];

    protected $casts = [
        'extraction_time' => 'float',
        'file_size' => 'integer',
        'pages_count' => 'integer'
    ];

    /**
     * علاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة مع معلومات الكتاب (للتوافق مع الكود الحالي)
     */
    public function bookInfo(): HasOne
    {
        return $this->hasOne(BookInfo::class);
    }

    /**
     * علاقة مع معلومات الكتب المتعددة حسب اللغة
     */
    public function bookInfos(): HasMany
    {
        return $this->hasMany(BookInfo::class);
    }

    /**
     * الحصول على معلومات الكتاب حسب اللغة
     */
    public function getBookInfoByLanguage($language = null)
    {
        if (!$language) {
            // إذا لم يتم تحديد اللغة، استخدم لغة المتصفح أو اللغة الافتراضية
            $language = LanguageHelper::getPreferredLanguage();
        }

        // التحقق من صحة اللغة
        if (!LanguageHelper::isValidLanguage($language)) {
            $language = LanguageHelper::getPreferredLanguage();
        }

        // البحث عن معلومات الكتاب باللغة المحددة
        $bookInfo = $this->bookInfos()
            ->where('language', $language)
            ->first();

        if ($bookInfo) {
            return $bookInfo;
        }

        // إذا لم يتم العثور على معلومات باللغة المحددة، ابحث عن اللغة الافتراضية
        $defaultLanguage = LanguageHelper::getPreferredLanguage();
        $defaultBookInfo = $this->bookInfos()
            ->where('language', $defaultLanguage)
            ->first();

        if ($defaultBookInfo) {
            return $defaultBookInfo;
        }

        // إذا لم يتم العثور على أي معلومات، ارجع أول مدخل متوفر
        return $this->bookInfos()->first();
    }

    /**
     * الحصول على جميع اللغات المتوفرة لمعلومات الكتاب
     */
    public function getAvailableBookInfoLanguages()
    {
        return $this->bookInfos()
            ->pluck('language')
            ->unique()
            ->values();
    }

    /**
     * علاقة مع النصوص المحسنة
     */
    public function enhancedTexts()
    {
        return $this->hasMany(EnhancedText::class);
    }

    /**
     * علاقة مع النصوص المترجمة
     */
    public function translatedTexts()
    {
        return $this->hasMany(TranslatedText::class);
    }

    /**
     * علاقة مع النصوص الملخصة
     */
    public function summarizedTexts()
    {
        return $this->hasMany(SummarizedText::class);
    }

    /**
     * علاقة مع مقالات المدونة
     */
    public function blogArticles()
    {
        return $this->hasMany(BlogArticle::class);
    }
}
