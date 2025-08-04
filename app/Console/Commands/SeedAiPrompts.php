<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiPrompt;

class SeedAiPrompts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:seed-prompts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إضافة التوجيهات الافتراضية للذكاء الاصطناعي';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء إضافة التوجيهات الافتراضية...');

        $prompts = [
            // تحسين النص - العربية
            [
                'name' => 'تحسين النص العربي',
                'description' => 'توجيه لتحسين النص العربي وجعله أكثر وضوحاً وسلاسته',
                'language' => 'arabic',
                'prompt_type' => 'enhance',
                'prompt_text' => 'قم بتحسين النص التالي وجعله أكثر وضوحاً وسلاسته مع الحفاظ على المعنى الأصلي. احرص على تصحيح الأخطاء النحوية والإملائية إن وجدت.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تحسين النص - الإنجليزية
            [
                'name' => 'Enhance English Text',
                'description' => 'Prompt to enhance English text and make it more clear and fluent',
                'language' => 'english',
                'prompt_type' => 'enhance',
                'prompt_text' => 'Please enhance the following text and make it more clear and fluent while preserving the original meaning. Correct any grammatical and spelling errors if found.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // ترجمة النص - العربية
            [
                'name' => 'ترجمة إلى العربية',
                'description' => 'توجيه لترجمة النص إلى اللغة العربية',
                'language' => 'arabic',
                'prompt_type' => 'translate',
                'prompt_text' => 'قم بترجمة النص التالي إلى اللغة العربية مع الحفاظ على المعنى والدقة في الترجمة.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // ترجمة النص - الإنجليزية
            [
                'name' => 'Translate to English',
                'description' => 'Prompt to translate text to English',
                'language' => 'english',
                'prompt_type' => 'translate',
                'prompt_text' => 'Please translate the following text to English while preserving the meaning and accuracy of the translation.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تلخيص النص - العربية
            [
                'name' => 'تلخيص النص العربي',
                'description' => 'توجيه لتلخيص النص العربي مع الحفاظ على الأفكار الرئيسية',
                'language' => 'arabic',
                'prompt_type' => 'summarize',
                'prompt_text' => 'قم بتلخيص النص التالي مع الحفاظ على الأفكار الرئيسية والمعلومات المهمة. اجعل الملخص واضحاً ومختصراً.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تلخيص النص - الإنجليزية
            [
                'name' => 'Summarize English Text',
                'description' => 'Prompt to summarize English text while preserving main ideas',
                'language' => 'english',
                'prompt_type' => 'summarize',
                'prompt_text' => 'Please summarize the following text while preserving the main ideas and important information. Make the summary clear and concise.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تحسين اللغة - العربية
            [
                'name' => 'تحسين اللغة العربية',
                'description' => 'توجيه لتحسين اللغة العربية وتصحيح الأخطاء النحوية',
                'language' => 'arabic',
                'prompt_type' => 'improve_language',
                'prompt_text' => 'قم بتحسين اللغة في النص التالي وتصحيح الأخطاء النحوية والإملائية مع الحفاظ على المعنى الأصلي.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تحسين اللغة - الإنجليزية
            [
                'name' => 'Improve English Language',
                'description' => 'Prompt to improve English language and correct grammatical errors',
                'language' => 'english',
                'prompt_type' => 'improve_language',
                'prompt_text' => 'Please improve the language in the following text and correct any grammatical and spelling errors while preserving the original meaning.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تلخيص النص على هيئة نقاط - العربية
            [
                'name' => 'تلخيص النص على هيئة نقاط',
                'description' => 'توجيه لتلخيص النص على هيئة نقاط واضحة ومنظمة',
                'language' => 'arabic',
                'prompt_type' => 'improve_format',
                'prompt_text' => 'قم بتلخيص النص التالي على هيئة نقاط واضحة ومنظمة. يجب أن يكون التلخيص:\n\n1. مكتوب باللغة: العربية\n2. منظم في نقاط مرقمة أو مرقمة بالرموز\n3. يغطي الأفكار الرئيسية والفرعية\n4. سهل القراءة والفهم\n5. يحافظ على التسلسل المنطقي للمحتوى\n\nالنص المراد تلخيصه:\n\n{text}\n\nقم بإخراج التلخيص بالشكل التالي:\nالعنوان: [عنوان مختصر للتلخيص]\nالنقاط:\n• [النقطة الأولى]\n• [النقطة الثانية]\n• [النقطة الثالثة]\n... وهكذا',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // تلخيص النص على هيئة نقاط - الإنجليزية
            [
                'name' => 'Summarize Text as Bullet Points',
                'description' => 'Prompt to summarize text as clear and organized bullet points',
                'language' => 'english',
                'prompt_type' => 'improve_format',
                'prompt_text' => 'Please summarize the following text in the form of clear and organized bullet points. The summary should be:\n\n1. Written in: English\n2. Organized in numbered or bulleted points\n3. Cover main and sub-ideas\n4. Easy to read and understand\n5. Maintain logical sequence of content\n\nText to summarize:\n\n{text}\n\nPlease output the summary in the following format:\nTitle: [Brief summary title]\nPoints:\n• [First point]\n• [Second point]\n• [Third point]\n... and so on',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // استخراج معلومات الكتاب - العربية
            [
                'name' => 'استخراج معلومات الكتاب',
                'description' => 'توجيه لاستخراج معلومات الكتاب من النص',
                'language' => 'arabic',
                'prompt_type' => 'extract_info',
                'prompt_text' => 'قم باستخراج معلومات الكتاب التالية من النص: عنوان الكتاب، اسم المؤلف، نبذة مختصرة عن الكتاب. اكتب المعلومات باللغة العربية.',
                'is_active' => true,
                'is_default' => true,
            ],
            
            // استخراج معلومات الكتاب - الإنجليزية
            [
                'name' => 'Extract Book Information',
                'description' => 'Prompt to extract book information from text',
                'language' => 'english',
                'prompt_type' => 'extract_info',
                'prompt_text' => 'Please extract the following book information from the text: book title, author name, brief summary of the book. Write the information in English.',
                'is_active' => true,
                'is_default' => true,
            ],
        ];

        $count = 0;
        foreach ($prompts as $promptData) {
            // تحقق من وجود التوجيه
            $existingPrompt = AiPrompt::where('name', $promptData['name'])
                ->where('language', $promptData['language'])
                ->where('prompt_type', $promptData['prompt_type'])
                ->first();

            if (!$existingPrompt) {
                AiPrompt::create($promptData);
                $count++;
                $this->info("تم إضافة: {$promptData['name']}");
            } else {
                $this->line("موجود مسبقاً: {$promptData['name']}");
            }
        }

        $this->info("تم إضافة {$count} توجيه جديد بنجاح!");
        $this->info('تم الانتهاء من إضافة التوجيهات الافتراضية.');
    }
} 