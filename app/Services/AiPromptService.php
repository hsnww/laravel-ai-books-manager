<?php

namespace App\Services;

use App\Models\AiPrompt;

class AiPromptService
{
    /**
     * الحصول على البرومبت حسب نوع المعالجة واللغة
     */
    public function getPrompt(string $processType, string $language): ?AiPrompt
    {
        return AiPrompt::where('prompt_type', $processType)
            ->where('language', $language)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }
    
    /**
     * الحصول على جميع البرومبتات النشطة
     */
    public function getActivePrompts(): array
    {
        return AiPrompt::where('is_active', true)
            ->orderBy('language')
            ->orderBy('prompt_type')
            ->get()
            ->toArray();
    }
    
    /**
     * الحصول على البرومبتات حسب اللغة
     */
    public function getPromptsByLanguage(string $language): array
    {
        return AiPrompt::where('language', $language)
            ->where('is_active', true)
            ->orderBy('prompt_type')
            ->get()
            ->toArray();
    }
    
    /**
     * الحصول على البرومبتات حسب نوع المعالجة
     */
    public function getPromptsByType(string $processType): array
    {
        return AiPrompt::where('prompt_type', $processType)
            ->where('is_active', true)
            ->orderBy('language')
            ->get()
            ->toArray();
    }
} 