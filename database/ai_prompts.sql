-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 03, 2025 at 08:39 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ai_books_manager_laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_prompts`
--

CREATE TABLE `ai_prompts` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم التوجيه',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'وصف التوجيه',
  `language` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'arabic' COMMENT 'لغة التوجيه (arabic/english)',
  `prompt_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'نوع التوجيه (enhance/translate/summarize/improve_language/improve_format/extract_info)',
  `prompt_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'نص التوجيه',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'هل التوجيه نشط',
  `is_default` tinyint(1) DEFAULT '0' COMMENT 'هل هو التوجيه الافتراضي',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'معرف المستخدم الذي أنشأ التوجيه',
  `updated_by` int DEFAULT NULL COMMENT 'معرف المستخدم الذي عدل التوجيه'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_prompts`
--

INSERT INTO `ai_prompts` (`id`, `name`, `description`, `language`, `prompt_type`, `prompt_text`, `is_active`, `is_default`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'تحسين النص العربي', 'تحسين النص العربي (لغة + تنسيق)', 'arabic', 'enhance', 'أنت محرر نصوص {language} محترف متخصص في تحسين الكتب والمحتوى العلمي. مهمتك:\r\n\r\n**تعليمات التحسين:**\r\n- حسّن جودة النص {language}\r\n- صحح الأخطاء النحوية والإملائية\r\n- حسن بناء الجمل والتراكيب اللغوية\r\n- حسّن المفردات واستخدام الكلمات المناسبة\r\n- احتفظ بالمعنى الأصلي والنبرة\r\n- اجعل النص أكثر وضوحاً وسلاسة\r\n- أنشئ عنواناً مناسباً للنص المحسن\r\n- لا تضيف عبارات مثل \"سأقوم بتحسين\" أو \"هذا النص المحسن\"\r\n- اكتب النص المحسن مباشرة باللغة {language}\r\n\r\n**التنسيق المطلوب:**\r\nالعنوان: [عنوان النص المحسن]\r\nالنص: [النص المحسن]\r\n\r\n**النص المراد تحسينه:**\r\n{text}', 1, 1, '2025-07-11 12:48:16', '2025-08-02 19:44:36', NULL, 1),
(2, 'ترجمة النص العربي', 'توجيه لترجمة النصوص العربية', 'arabic', 'translate', 'أنت مترجم محترف متخصص في الترجمة. مهمتك ترجمة النص التالي:\r\n\r\n**تعليمات الترجمة:**\r\n- ترجم النص إلى اللغة {language}\r\n- احتفظ بالمعنى الأصلي والنبرة\r\n- اجعل الترجمة طبيعية ومناسبة للناطقين الأصليين\r\n- أنشئ عنواناً مناسباً للنص المترجم\r\n- لا تضيف عبارات مثل \"سأقوم بترجمة\" أو \"هذه الترجمة\"\r\n- اكتب الترجمة مباشرة كنص منسق\r\n\r\n**التنسيق المطلوب:**\r\nالعنوان: [عنوان النص المترجم]\r\nالنص: [النص المترجم]\r\n\r\n**النص المراد ترجمته:**\r\n{text}', 1, 1, '2025-07-11 12:48:16', '2025-08-02 19:44:37', NULL, 1),
(3, 'تلخيص النص العربي', 'توجيه لتلخيص النصوص العربية', 'arabic', 'summarize', 'أنت محرر محترف متخصص في التلخيص. مهمتك تلخيص النص التالي:\r\n\r\n**تعليمات التلخيص:**\r\n- أنشئ ملخصاً مقتضباً للنص باللغة {language}\r\n- احتفظ بالأفكار الرئيسية والنقاط المهمة\r\n- اختصر النص بشكل كبير مع الحفاظ على التدفق المنطقي\r\n- أنشئ عنواناً مناسباً للملخص\r\n- لا تضيف عبارات مثل \"هذا ملخص\" أو \"سأقوم بتلخيص\"\r\n- اكتب الملخص مباشرة كنص منسق\r\n\r\n**التنسيق المطلوب:**\r\nالعنوان: [عنوان الملخص]\r\nالملخص: [النص الملخص]\r\n\r\n**النص المراد تلخيصه:**\r\n{text}', 1, 1, '2025-07-11 12:48:16', '2025-08-02 19:44:37', NULL, 1),
(4, 'summarize text in bullet points', 'تحسين تنسيق النص الإنجليزي فقط', 'english', 'improve_format', 'Please summarize the following text in the form of clear and organized bullet points. The summary should be:\n\n1. Written in: {language}\n2. Organized in numbered or bulleted points\n3. Cover main and sub-ideas\n4. Easy to read and understand\n5. Maintain logical sequence of content\n\nText to summarize:\n\n{text}\n\nPlease output the summary in the following format:\nTitle: [Brief summary title]\nPoints:\n• [First point]\n• [Second point]\n• [Third point]\n... and so on', 1, 1, '2025-07-11 12:48:16', '2025-08-03 05:10:35', NULL, 1),
(5, 'Translate English Text', 'Prompt for translating English texts', 'english', 'translate', 'You are a professional translator. Your task is to translate the following text:\r\n\r\n**Translation Instructions:**\r\n- Translate the text to {language}\r\n- Preserve the original meaning and tone\r\n- Make the translation natural and appropriate for native speakers\r\n- Create an appropriate title for the translated text\r\n- Don\'t add phrases like \"I will translate\" or \"This translation\"\r\n- Write the translation directly as formatted text\r\n\r\n**Required Format:**\r\nTitle: [Translated Text Title]\r\nText: [Translated Text]\r\n\r\n**Text to translate:**\r\n{text}', 1, 1, '2025-07-11 12:48:16', '2025-08-02 19:44:37', NULL, 1),
(6, 'Summarize English Text', 'Prompt for summarizing English texts', 'english', 'summarize', 'You are a professional editor specializing in summarization. Your task is to summarize the following text:\r\n\r\n**Summarization Instructions:**\r\n- Create a concise summary of the text in {language}\r\n- Preserve main ideas and important points\r\n- Significantly shorten the text while maintaining logical flow\r\n- Create an appropriate title for the summary\r\n- Don\'t add phrases like \"This is a summary\" or \"I will summarize\"\r\n- Write the summary directly as formatted text\r\n\r\n**Required Format:**\r\nTitle: [Summary Title]\r\nSummary: [Summarized Text]\r\n\r\n**Text to summarize:**\r\n{text}', 1, 1, '2025-07-11 12:48:16', '2025-08-02 19:44:37', NULL, 1),
(15, 'استخراج معلومات الكتاب العربي', 'توجيه لاستخراج معلومات الكتاب والمؤلف من النص العربي', 'arabic', 'extract_info', 'أنت متخصص في استخراج معلومات الكتب. مهمتك إنشاء معلومات الكتاب من النص التالي:\n\n**المعلومات المطلوبة:**\n- عنوان الكتاب\n- اسم المؤلف\n- ملخص مختصر\n\n**تعليمات الاستخراج:**\n- أنشئ معلومات الكتاب باللغة {language}\n- إذا لم تجد معلومة، اكتب \"غير محدد\"\n- اكتب المعلومات مباشرة باللغة المطلوبة\n\n**النص المراد استخراج المعلومات منه:**\n{text}\n\n**التنسيق المطلوب:**\nالعنوان: [عنوان الكتاب]\nالمؤلف: [اسم المؤلف]\nالملخص: [ملخص مختصر]', 1, 1, '2025-07-13 10:15:27', '2025-08-01 12:58:30', NULL, 1),
(17, 'Improve English Formatting', 'تحسين النص الإنجليزي (لغة + تنسيق)', 'english', 'enhance', 'You are a professional {language} text editor specializing in improving books and academic content. Your task:\r\n\r\n**Enhancement Instructions:**\r\n- Improve {language} text quality\r\n- Correct grammar and spelling errors\r\n- Improve sentence structure and linguistic constructions\r\n- Enhance vocabulary and use appropriate words\r\n- Preserve the original meaning and tone\r\n- Make the text clearer and more fluent\r\n- Create an appropriate title for the enhanced text\r\n- Don\'\'t add phrases like \"I will enhance\" or \"This enhanced text\"\r\n- Write the enhanced text directly in {language}\r\n\r\n**Required Format:**\r\nTitle: [Enhanced Text Title]\r\nText: [Enhanced Text]\r\n\r\n**Text to enhance:**\r\n{text}', 1, 1, '2025-07-13 10:15:27', '2025-08-02 19:44:37', NULL, 1),
(19, 'Extract book information', 'Extract book information', 'english', 'extract_info', 'You are a book information extraction specialist. Your task is to create book information from the following text:\n\n**Required Information:**\n- Book title\n- Author name\n- Brief summary\n\n**Extraction Instructions:**\n- Create book information in {language}\n- If information is not found, write \"Not specified\"\n- Write the information directly in the target language\n\n**Text to extract information from:**\n{text}\n\n**Required Format:**\nTitle: [Book Title]\nAuthor: [Author Name]\nSummary: [Brief Summary]', 1, 1, '2025-07-14 02:13:28', '2025-08-01 12:58:45', 1, 1),
(20, 'بتلخيص النص التالي على هيئة نقاط', 'يطلب هذا التوجيه من معالج الذكاء الاصطناعي تلخيص النص على هيئة نقاط وفق اللغة المحددة للاخراج', 'arabic', 'improve_format', 'قم بتلخيص النص التالي على هيئة نقاط واضحة ومنظمة. يجب أن يكون التلخيص:\n\n1. مكتوب باللغة: {language}\n2. منظم في نقاط مرقمة أو مرقمة بالرموز\n3. يغطي الأفكار الرئيسية والفرعية\n4. سهل القراءة والفهم\n5. يحافظ على التسلسل المنطقي للمحتوى\n\nالنص المراد تلخيصه:\n\n{text}\n\nقم بإخراج التلخيص بالشكل التالي:\nالعنوان: [عنوان مختصر للتلخيص]\nالنقاط:\n• [النقطة الأولى]\n• [النقطة الثانية]\n• [النقطة الثالثة]\n... وهكذا', 1, 1, '2025-07-14 02:21:34', '2025-08-03 05:11:11', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_prompt_type` (`prompt_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
