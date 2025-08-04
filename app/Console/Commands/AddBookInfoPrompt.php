<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiPrompt;

class AddBookInfoPrompt extends Command
{
    protected $signature = 'add:book-info-prompt';
    protected $description = 'Add book info extraction prompt to database';

    public function handle()
    {
        $this->info('Adding book info extraction prompts to database...');
        
        // Arabic prompt
        $arabicPrompt = AiPrompt::updateOrCreate(
            [
                'prompt_type' => 'extract_book_info',
                'language' => 'arabic'
            ],
            [
                'prompt_text' => "أنت متخصص في استخراج معلومات الكتب والمؤلفين. مهمتك استخراج المعلومات التالية من النص:

**المعلومات المطلوبة:**
- عنوان الكتاب
- اسم المؤلف ويجب ان يكون اسم شخص
- نبذة عن الكتاب
- تفاصيل النشر
- المواضيع الرئيسية
- الجمهور المستهدف
- النقاط الرئيسية
- معلومات عن المؤلف

**تعليمات الاستخراج:**
- استخرج المعلومات بدقة من النص
- اكتب المعلومات باللغة العربية
- نظّم المعلومات بشكل واضح ومنظم
- إذا لم تجد معلومة معينة، اكتب \"غير محدد\"
- لا تضيف عبارات مثل \"سأقوم باستخراج\"

**النص المراد استخراج المعلومات منه:**
{text}

أجب بالتنسيق التالي:
العنوان: [عنوان الكتاب]
المؤلف: [اسم المؤلف]
الملخص: [نبذة عن الكتاب]
تفاصيل النشر: [تفاصيل النشر]
المواضيع الرئيسية: [المواضيع الرئيسية]
الجمهور المستهدف: [الجمهور المستهدف]
النقاط الرئيسية: [النقاط الرئيسية]
معلومات عن المؤلف: [معلومات عن المؤلف]",
                'description' => 'استخراج معلومات الكتاب باللغة العربية',
                'is_active' => true,
                'default_language' => 'arabic'
            ]
        );
        
        // English prompt
        $englishPrompt = AiPrompt::updateOrCreate(
            [
                'prompt_type' => 'extract_book_info',
                'language' => 'english'
            ],
            [
                'prompt_text' => "You are a specialist in extracting book and author information. Your task is to extract the following information from the text:

**Required Information:**
- Book title
- Author name
- Book summary
- Publication details
- Main topics
- Target audience
- Key points
- Author information

**Extraction Instructions:**
- Extract information accurately from the text
- Write the information in English
- Organize information clearly and systematically
- If you don't find specific information, write \"Not specified\"
- Don't add phrases like \"I will extract\"

**Text to extract information from:**
{text}

Answer in the following format:
Title: [Book Title]
Author: [Author Name]
Summary: [Book Summary]
Publication Details: [Publication Details]
Main Topics: [Main Topics]
Target Audience: [Target Audience]
Key Points: [Key Points]
Author Information: [Author Information]",
                'description' => 'Extract book information in English',
                'is_active' => true,
                'default_language' => 'english'
            ]
        );
        
        $this->info('✅ Arabic prompt ID: ' . $arabicPrompt->id);
        $this->info('✅ English prompt ID: ' . $englishPrompt->id);
        $this->info('Book info extraction prompts added successfully!');
        
        return 0;
    }
} 