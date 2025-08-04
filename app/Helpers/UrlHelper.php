<?php

namespace App\Helpers;

class UrlHelper
{
    /**
     * إنشاء URL آمن للملفات
     */
    public static function generateFileUrl(string $relativePath): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/storage/' . ltrim($relativePath, '/');
    }
    
    /**
     * إنشاء URL آمن للملفات المستخرجة
     */
    public static function generateExtractedFileUrl(string $relativePath): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/storage/extracted_texts/' . ltrim($relativePath, '/');
    }
    
    /**
     * تنظيف المسار لاستخدام forward slash
     */
    public static function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
    
    /**
     * التحقق من صحة URL
     */
    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
} 