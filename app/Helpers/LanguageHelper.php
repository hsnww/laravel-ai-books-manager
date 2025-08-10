<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageHelper
{
    /**
     * الحصول على اللغة الحالية
     */
    public static function getCurrentLanguage()
    {
        return App::getLocale();
    }

    /**
     * تحديد اللغة
     */
    public static function setLanguage($language)
    {
        if (in_array($language, ['ar', 'en'])) {
            App::setLocale($language);
            Session::put('locale', $language);
            return true;
        }
        return false;
    }

    /**
     * الحصول على اتجاه النص (RTL/LTR)
     */
    public static function getTextDirection()
    {
        return App::getLocale() === 'ar' ? 'rtl' : 'ltr';
    }

    /**
     * التحقق من كون اللغة RTL
     */
    public static function isRtlLanguage($language = null)
    {
        if (!$language) {
            $language = self::getCurrentLanguage();
        }
        
        $rtlLanguages = ['ar', 'he', 'fa', 'ur', 'sd', 'ks', 'ps', 'prs'];
        return in_array($language, $rtlLanguages);
    }

    /**
     * الحصول على اسم اللغة بالعربية
     */
    public static function getLanguageName($language)
    {
        $languages = [
            'ar' => 'العربية',
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
            'tr' => 'Türkçe',
            'fa' => 'فارسی',
            'ur' => 'اردو',
            'hi' => 'हिन्दी',
            'bn' => 'বাংলা'
        ];
        
        return $languages[$language] ?? $language;
    }

    /**
     * الحصول على اللغة المفضلة حسب المتصفح
     */
    public static function getPreferredLanguage()
    {
        $locale = App::getLocale();
        
        // إذا كانت اللغة العربية، استخدم العربية
        if ($locale === 'ar') {
            return 'Arabic';
        }
        
        // وإلا استخدم الإنجليزية
        return 'English';
    }

    /**
     * تحديث معلومات الكتاب حسب اللغة
     */
    public static function updateBookInfoForLanguage($bookId, $language)
    {
        // هذا سيتم استدعاؤه من JavaScript
        return route('books.info-by-language', ['bookIdentify' => $bookId, 'language' => $language]);
    }

    /**
     * الحصول على اتجاه النص للغة محددة
     */
    public static function getTextDirectionForLanguage($language)
    {
        return self::isRtlLanguage($language) ? 'rtl' : 'ltr';
    }

    /**
     * التحقق من صحة اللغة
     */
    public static function isValidLanguage($language)
    {
        // قائمة بأسماء اللغات المدعومة (عربية وإنجليزية)
        $supportedLanguages = [
            // أسماء عربية
            'العربية', 'الإنجليزية', 'الفرنسية', 'الإسبانية', 'الألمانية', 'الإيطالية',
            'البرتغالية', 'الروسية', 'الصينية', 'اليابانية', 'الكورية', 'التركية',
            'الفارسية', 'الأردية', 'الهندية', 'البنغالية',
            // أسماء إنجليزية
            'Arabic', 'English', 'French', 'Spanish', 'German', 'Italian',
            'Portuguese', 'Russian', 'Chinese', 'Japanese', 'Korean', 'Turkish',
            'Persian', 'Urdu', 'Hindi', 'Bengali',
            // رموز مختصرة
            'ar', 'en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko', 'tr', 'fa', 'ur', 'hi', 'bn'
        ];
        
        return in_array($language, $supportedLanguages);
    }

    /**
     * الحصول على اللغة المعاكسة
     */
    public static function getOppositeLanguage($language = null)
    {
        if (!$language) {
            $language = self::getCurrentLanguage();
        }
        return $language === 'ar' ? 'en' : 'ar';
    }

    /**
     * الحصول على اسم اللغة باللغة الحالية
     */
    public static function getLocalizedLanguageName($language)
    {
        $currentLocale = self::getCurrentLanguage();
        
        if ($currentLocale === 'ar') {
            $arabicNames = [
                'ar' => 'العربية',
                'en' => 'الإنجليزية',
                'fr' => 'الفرنسية',
                'es' => 'الإسبانية',
                'de' => 'الألمانية',
                'it' => 'الإيطالية',
                'pt' => 'البرتغالية',
                'ru' => 'الروسية',
                'zh' => 'الصينية',
                'ja' => 'اليابانية',
                'ko' => 'الكورية',
                'tr' => 'التركية',
                'fa' => 'الفارسية',
                'ur' => 'الأردية',
                'hi' => 'الهندية',
                'bn' => 'البنغالية'
            ];
            return $arabicNames[$language] ?? $language;
        } else {
            $englishNames = [
                'ar' => 'Arabic',
                'en' => 'English',
                'fr' => 'French',
                'es' => 'Spanish',
                'de' => 'German',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'zh' => 'Chinese',
                'ja' => 'Japanese',
                'ko' => 'Korean',
                'tr' => 'Turkish',
                'fa' => 'Persian',
                'ur' => 'Urdu',
                'hi' => 'Hindi',
                'bn' => 'Bengali'
            ];
            return $englishNames[$language] ?? $language;
        }
    }

    /**
     * تحويل اسم اللغة من العربية إلى الإنجليزية
     */
    public static function normalizeLanguageName($language)
    {
        // إذا كانت اللغة فارغة أو null
        if (empty($language)) {
            \Log::warning('LanguageHelper: Empty language provided to normalizeLanguageName');
            return 'English'; // اللغة الافتراضية
        }
        
        $languageMapping = [
            'العربية' => 'Arabic',
            'الإنجليزية' => 'English',
            'الفرنسية' => 'French',
            'الإسبانية' => 'Spanish',
            'الألمانية' => 'German',
            'الإيطالية' => 'Italian',
            'البرتغالية' => 'Portuguese',
            'الروسية' => 'Russian',
            'الصينية' => 'Chinese',
            'اليابانية' => 'Japanese',
            'الكورية' => 'Korean',
            'التركية' => 'Turkish',
            'الفارسية' => 'Persian',
            'الأردية' => 'Urdu',
            'الهندية' => 'Hindi',
            'البنغالية' => 'Bengali'
        ];
        
        $normalizedLanguage = $languageMapping[$language] ?? $language;
        
        // Log the normalization for debugging
        if ($normalizedLanguage !== $language) {
            \Log::info("LanguageHelper: Normalized language from '{$language}' to '{$normalizedLanguage}'");
        } else {
            \Log::info("LanguageHelper: Language '{$language}' was already normalized");
        }
        
        return $normalizedLanguage;
    }

    /**
     * تطبيع اسم اللغة مع fallback mechanism
     */
    public static function normalizeLanguageNameWithFallback($language, $fallbackLanguage = 'English')
    {
        // تطبيع اللغة
        $normalizedLanguage = self::normalizeLanguageName($language);
        
        // التحقق من صحة اللغة المطبيع
        if (self::isValidLanguage($normalizedLanguage)) {
            return $normalizedLanguage;
        }
        
        // إذا لم تكن اللغة صحيحة، استخدم اللغة البديلة
        \Log::warning("LanguageHelper: Invalid language '{$language}' normalized to '{$normalizedLanguage}', using fallback '{$fallbackLanguage}'");
        return $fallbackLanguage;
    }

    /**
     * الحصول على قائمة اللغات المدعومة
     */
    public static function getSupportedLanguages()
    {
        return [
            'Arabic' => 'العربية',
            'English' => 'English',
            'French' => 'Français',
            'Spanish' => 'Español',
            'German' => 'Deutsch',
            'Italian' => 'Italiano',
            'Portuguese' => 'Português',
            'Russian' => 'Русский',
            'Chinese' => '中文',
            'Japanese' => '日本語',
            'Korean' => '한국어',
            'Turkish' => 'Türkçe',
            'Persian' => 'فارسی',
            'Urdu' => 'اردو',
            'Hindi' => 'हिन्दी',
            'Bengali' => 'বাংলা'
        ];
    }

    /**
     * تحويل اسم اللغة من الإنجليزية إلى العربية
     */
    public static function arabizeLanguageName($language)
    {
        $reverseMapping = array_flip([
            'العربية' => 'Arabic',
            'الإنجليزية' => 'English',
            'الفرنسية' => 'French',
            'الإسبانية' => 'Spanish',
            'الألمانية' => 'German',
            'الإيطالية' => 'Italian',
            'البرتغالية' => 'Portuguese',
            'الروسية' => 'Russian',
            'الصينية' => 'Chinese',
            'اليابانية' => 'Japanese',
            'الكورية' => 'Korean',
            'التركية' => 'Turkish',
            'الفارسية' => 'Persian',
            'الأردية' => 'Urdu',
            'الهندية' => 'Hindi',
            'البنغالية' => 'Bengali'
        ]);
        
        return $reverseMapping[$language] ?? $language;
    }

    /**
     * تحويل اسم اللغة من العربية إلى الإنجليزية للنصوص المعالجة
     */
    public static function normalizeProcessedTextLanguage($language)
    {
        $languageMapping = [
            'العربية' => 'Arabic', 'الإنجليزية' => 'English', 'الفرنسية' => 'French',
            'الإسبانية' => 'Spanish', 'الألمانية' => 'German', 'الإيطالية' => 'Italian',
            'البرتغالية' => 'Portuguese', 'الروسية' => 'Russian', 'الصينية' => 'Chinese',
            'اليابانية' => 'Japanese', 'الكورية' => 'Korean', 'التركية' => 'Turkish',
            'الفارسية' => 'Persian', 'الأردية' => 'Urdu', 'الهندية' => 'Hindi', 'البنغالية' => 'Bengali',
            'Arabic' => 'Arabic', 'English' => 'English', 'French' => 'French', 'Spanish' => 'Spanish',
            'German' => 'German', 'Italian' => 'Italian', 'Portuguese' => 'Portuguese',
            'Russian' => 'Russian', 'Chinese' => 'Chinese', 'Japanese' => 'Japanese',
            'Korean' => 'Korean', 'Turkish' => 'Turkish', 'Persian' => 'Persian',
            'Urdu' => 'Urdu', 'Hindi' => 'Hindi', 'Bengali' => 'Bengali'
        ];
        
        return $languageMapping[$language] ?? $language;
    }

    /**
     * الحصول على خيارات اللغات للنماذج
     */
    public static function getLanguageOptionsForForms()
    {
        return [
            'Arabic' => 'العربية',
            'English' => 'English',
            'French' => 'Français',
            'Spanish' => 'Español',
            'German' => 'Deutsch',
            'Italian' => 'Italiano',
            'Portuguese' => 'Português',
            'Russian' => 'Русский',
            'Chinese' => '中文',
            'Japanese' => '日本語',
            'Korean' => '한국어',
            'Turkish' => 'Türkçe',
            'Persian' => 'فارسی',
            'Urdu' => 'اردو',
            'Hindi' => 'हिन्दी',
            'Bengali' => 'বাংলা'
        ];
    }

    /**
     * الحصول على خيارات اللغات للنماذج (باللغة الإنجليزية)
     */
    public static function getLanguageOptionsForFormsEnglish()
    {
        return [
            'arabic' => 'العربية',
            'english' => 'English',
            'french' => 'Français',
            'german' => 'Deutsch',
            'spanish' => 'Español',
            'italian' => 'Italiano',
            'portuguese' => 'Português',
            'russian' => 'Русский',
            'chinese' => '中文',
            'japanese' => '日本語',
            'korean' => '한국어',
            'turkish' => 'Türkçe',
            'hindi' => 'हिन्दी',
            'urdu' => 'اردو'
        ];
    }
}
