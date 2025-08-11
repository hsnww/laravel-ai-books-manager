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
        $this->info('Seeding AI prompts...');
        
        // Clear existing prompts first
        AiPrompt::truncate();
        
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
                'is_default' => true,
            ],
            
            // استخراج معلومات الكتاب - الإنجليزية
            [
                'name' => 'Extract Book Information',
                'description' => 'Prompt to extract book information from text',
                'language' => 'english',
                'prompt_type' => 'extract_info',
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
                'is_default' => true,
            ],
            
            // كتابة مقال احترافي للمدونة - العربية (محسن)
            [
                'name' => 'كتابة مقال احترافي للمدونة - محسن',
                'description' => 'برومبت محسن لكتابة مقالات احترافية للمدونة مع شمول جميع الفصول واستبعاد نصوص العجر',
                'language' => 'arabic',
                'prompt_type' => 'blog_article',
                'prompt_text' => "أنت كاتب محترف متخصص في كتابة مقالات معرفية احترافية للمدونات. مهمتك كتابة مقال شامل ومفصل عن كتاب محدد.

**المتطلبات الأساسية:**
- **الحد الأدنى: 900 كلمة، الحد الأقصى: 2000 كلمة**
- **شمول جميع فصول الكتاب:** يجب أن تغطي المقالة جميع الفصول المتوفرة في النص، لا تتوقف عند فصل معين
- **استبعاد نصوص العجر:** تجاهل تماماً أي نصوص عجر أو فهارس أو قوائم مراجع، ركز فقط على المحتوى الأساسي للكتاب
- **تحليل شامل:** اقرأ النص كاملاً قبل البدء في الكتابة

**هيكل المقال المطلوب:**

**1. العنوان الرئيسي (H1):**
- عنوان جذاب ومقنع يعكس محتوى الكتاب
- استخدام كلمات مفتاحية طبيعية

**2. مقدمة قوية (200-300 كلمة):**
- تعريف بالكتاب ومجال تخصصه
- إشارة لأهمية الموضوع في الوقت الحالي
- إثارة فضول القارئ

**3. معلومات الكتاب الأساسية:**
- **عنوان الكتاب:** [العنوان الأصلي والعنوان المترجم إن وجد]
- **المؤلف:** [اسم المؤلف مع نبذة مختصرة عن سيرته إن توفرت]
- **التصنيف:** [تصنيف دقيق مثل: تنمية ذاتية، رواية، علم نفس، إدارة، اقتصاد، علوم اجتماعية، فلسفة، تاريخ، إلخ]
- **سنة النشر:** [إن توفرت]
- **الناشر:** [إن توفر]
- **عدد الصفحات:** [إن توفر]

**4. ملخص شامل للكتاب (300-400 كلمة):**
- عرض الأفكار الرئيسية والمواضيع المطروحة
- شرح المنهجية أو الأسلوب المستخدم
- إبراز النقاط المميزة والجديدة

**5. تحليل المحتوى (400-600 كلمة):**
- **الفصل الأول:** [تحليل مفصل]
- **الفصل الثاني:** [تحليل مفصل]
- **الفصل الثالث:** [تحليل مفصل]
- [استمر في تحليل جميع الفصول المتوفرة]

**6. النقاط الرئيسية (200-300 كلمة):**
- استخراج 5-7 نقاط رئيسية من الكتاب
- عرضها بشكل منظم ومقنع

**7. مقتبسات مختارة (2-3 مقتبسات):**
- اختيار مقتبسات قوية تعبر عن الأفكار الرئيسية
- إضافة سياق لكل مقتبس

**8. التقييم والخلاصة (200-300 كلمة):**
- تقييم شامل للكتاب
- تحديد الجمهور المستهدف
- توصية للقراء

**متطلبات SEO:**
- استخدام 4-5 عناوين فرعية (H2, H3)
- كل فقرة لا تزيد عن 250 كلمة
- استخدام كلمات مفتاحية طبيعية ومتكررة
- تنسيق سهل القراءة مع مسافات مناسبة

**الأسلوب:**
- لغة عربية فصيحة ومهنية
- أسلوب إعلامي محايد
- جمل واضحة ومباشرة
- تجنب التكرار والحشو

**تحذير مهم:**
- اقرأ النص كاملاً قبل البدء في الكتابة
- لا تتوقف عند فصل معين، استمر في تحليل جميع الفصول
- تجاهل تماماً نصوص العجر والفهارس والقوائم
- ركز فقط على المحتوى الأساسي للكتاب

استخدم النص التالي لكتابة المقال:

{text}

اكتب المقال باللغة العربية.",
                'is_active' => true,
                'is_default' => true,
            ],
            
            // كتابة مقال احترافي للمدونة - الإنجليزية (محسن)
            [
                'name' => 'Professional Blog Article Writing - Enhanced',
                'description' => 'Enhanced prompt for writing professional blog articles covering all chapters and excluding filler text',
                'language' => 'english',
                'prompt_type' => 'blog_article',
                'prompt_text' => "You are a professional content writer specializing in creating comprehensive blog articles for knowledge-based websites. Your task is to write a detailed and engaging article about a specific book.

**Core Requirements:**
- **Minimum: 900 words, Maximum: 2000 words**
- **Cover all book chapters:** The article must cover all available chapters in the text, do not stop at any specific chapter
- **Exclude filler text:** Completely ignore any filler text, indexes, or reference lists, focus only on the book's core content
- **Comprehensive analysis:** Read the entire text before starting to write

**Required Article Structure:**

**1. Main Headline (H1):**
- Compelling and convincing title that reflects the book's content
- Use natural keywords

**2. Strong Introduction (200-300 words):**
- Introduce the book and its field of expertise
- Reference the topic's current importance
- Arouse reader curiosity

**3. Basic Book Information:**
- **Book Title:** [Original title and translated title if available]
- **Author:** [Author name with brief biography if available]
- **Category:** [Accurate classification such as: self-development, novel, psychology, management, economics, social sciences, philosophy, history, etc.]
- **Publication Year:** [If available]
- **Publisher:** [If available]
- **Number of Pages:** [If available]

**4. Comprehensive Book Summary (300-400 words):**
- Present main ideas and topics discussed
- Explain methodology or approach used
- Highlight distinctive and new points

**5. Content Analysis (400-600 words):**
- **Chapter One:** [Detailed analysis]
- **Chapter Two:** [Detailed analysis]
- **Chapter Three:** [Detailed analysis]
- [Continue analyzing all available chapters]

**6. Key Points (200-300 words):**
- Extract 5-7 main points from the book
- Present them in an organized and convincing manner

**7. Selected Quotes (2-3 quotes):**
- Choose strong quotes that express main ideas
- Add context for each quote

**8. Evaluation and Conclusion (200-300 words):**
- Comprehensive book evaluation
- Identify target audience
- Reader recommendations

**SEO Requirements:**
- Use 4-5 subheadings (H2, H3)
- Each paragraph no more than 250 words
- Use natural and repeated keywords
- Easy-to-read formatting with appropriate spacing

**Writing Style:**
- Professional and clear English
- Informative and neutral tone
- Clear and direct sentences
- Avoid repetition and filler

**Important Warning:**
- Read the entire text before starting to write
- Do not stop at any specific chapter, continue analyzing all chapters
- Completely ignore filler text, indexes, and lists
- Focus only on the book's core content

Use the following text to write the article:

{text}

Write the article in English.",
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