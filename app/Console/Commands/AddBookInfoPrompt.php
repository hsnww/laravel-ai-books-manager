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
        
        // Arabic prompt - Updated to remove Arabic labels
        $arabicPrompt = AiPrompt::updateOrCreate(
            [
                'prompt_type' => 'extract_info',
                'language' => 'arabic'
            ],
            [
                'name' => 'استخراج معلومات الكتاب',
                'description' => 'توجيه لاستخراج معلومات الكتاب من النص',
                'prompt_text' => "أنت متخصص في استخراج معلومات الكتب. مهمتك إنشاء معلومات الكتاب من النص التالي:

المعلومات المطلوبة:
- عنوان الكتاب
- اسم المؤلف
- ملخص مختصر في حدود 200 كلمة

تعليمات الاستخراج:
- أنشئ جميع معلومات الكتاب باللغة {language} بما في ذلك عنوان الكتاب واسم المؤلف
- إذا لم تجد معلومة، اكتب \"غير محدد\" أو ما يقابلها باللغة المحددة
- اكتب المعلومات مباشرة باللغة المطلوبة
- اكتب ملخصاً مكتملاً ومفيداً دون انقطاع أو عبارات مثل \"بقية الملخص غير متوفرة\" أو \"يمكن استنتاج\"
- تأكد من أن الملخص ينتهي بجملة مكتملة ومنطقية
- لا تستخدم علامات الحذف (...) في نهاية الملخص

النص المراد استخراج المعلومات منه:
{text}

التنسيق المطلوب:
اكتب العنوان مباشرة في السطر الأول
اكتب اسم المؤلف في السطر الثاني
اكتب الملخص في السطر الثالث وما بعده

مثال:
عنوان الكتاب هنا
اسم المؤلف هنا
ملخص الكتاب هنا...",
                'is_active' => true,
                'is_default' => true
            ]
        );
        
        // English prompt - Updated to remove labels
        $englishPrompt = AiPrompt::updateOrCreate(
            [
                'prompt_type' => 'extract_info',
                'language' => 'english'
            ],
            [
                'name' => 'Extract Book Information',
                'description' => 'Prompt to extract book information from text',
                'prompt_text' => "You are a specialist in extracting book information. Your task is to create book information from the following text:

Required Information:
- Book title
- Author name
- Brief summary within 200 words

Extraction Instructions:
- Create all book information in {language} including book title and author name
- If you don't find information, write \"Not specified\" or its equivalent in the specified language
- Write information directly in the required language
- Write a complete and useful summary without interruption or phrases like \"remaining summary not available\" or \"can be inferred\"
- Ensure the summary ends with a complete and logical sentence
- Do not use ellipsis (...) at the end of the summary

Text to extract information from:
{text}

Required Format:
Write the title directly on the first line
Write the author name on the second line
Write the summary on the third line and onwards

Example:
Book Title Here
Author Name Here
Book summary here...",
                'is_active' => true,
                'is_default' => true
            ]
        );
        
        $this->info('✅ Arabic prompt ID: ' . $arabicPrompt->id);
        $this->info('✅ English prompt ID: ' . $englishPrompt->id);
        $this->info('Book info extraction prompts updated successfully!');
        
        return 0;
    }
} 