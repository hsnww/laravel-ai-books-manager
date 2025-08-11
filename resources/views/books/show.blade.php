<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Helpers\LanguageHelper::getTextDirection() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $book->book_identify }} - {{ __('AI Books Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .rtl { direction: rtl; }
        .ltr { direction: ltr; }
        .text-content { 
            white-space: pre-wrap; 
            line-height: 1.8; 
            font-size: 16px; 
            text-align: justify;
        }
        .text-content.rtl {
            font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif;
            text-align: right;
            line-height: 2;
        }
        .text-content.ltr {
            text-align: left;
        }
        .processing-type-card { transition: all 0.3s ease; }
        .processing-type-card:hover { transform: translateY(-2px); }
        .processing-type-card.active { 
            border-color: #3b82f6; 
            background-color: #eff6ff;
        }
        .language-tab { transition: all 0.3s ease; }
        .language-tab.active { 
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            transform: scale(1.05);
        }
        .text-display { transition: all 0.3s ease; }
        .text-display.fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="mr-4 p-2 rounded-full bg-blue-100 hover:bg-blue-200 transition duration-200">
                        <i class="fas fa-arrow-left text-blue-600"></i>
                    </a>
                    <i class="fas fa-brain text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ __('AI Books Manager') }}
                        </h1>
                        <p class="text-sm text-gray-600">{{ $book->book_identify }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <!-- Language Switcher -->
                    <div class="relative">
                        <select id="language-switcher" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ar" {{ \App\Helpers\LanguageHelper::getCurrentLanguage() == 'ar' ? 'selected' : '' }}>العربية</option>
                            <option value="en" {{ \App\Helpers\LanguageHelper::getCurrentLanguage() == 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>
                    
                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <nav class="flex items-center space-x-4 rtl:space-x-reverse">
                            <a href="{{ route('books.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-books mr-2"></i>
                                {{ __('Books') }}
                            </a>
                            @auth
                                @if(auth()->user()->hasRole('super_admin'))
                                    <a href="{{ route('filament.admin.pages.dashboard') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-tachometer-alt mr-2"></i>
                                        {{ __('Dashboard') }}
                                    </a>
                                    <a href="{{ route('ai-processor.show', $book->book_identify) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-cogs mr-2"></i>
                                        {{ __('Process') }}
                                    </a>
                                    <a href="{{ route('file-manager.show', $book->book_identify) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-folder mr-2"></i>
                                        {{ __('Files') }}
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    {{ __('Login') }}
                                </a>
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Book Information -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Book Info -->
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                        {{ __('Book Information') }}
                    </h2>
                    
                    @php
                        // الحصول على معلومات الكتاب حسب اللغة المحددة
                        $currentBookInfo = $preferredBookInfo ?? $book->getBookInfoByLanguage($selectedLanguage);
                    @endphp
                    
                    @if($currentBookInfo)
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-book-open text-blue-500 mr-3"></i>
                                <div>
                                    <span class="font-semibold text-gray-700">{{ __('Title') }}:</span>
                                    <span class="text-gray-900 ml-2">{{ $currentBookInfo->title }}</span>
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-user text-green-500 mr-3"></i>
                                <div>
                                    <span class="font-semibold text-gray-700">{{ __('Author') }}:</span>
                                    <span class="text-gray-900 ml-2">{{ $currentBookInfo->author }}</span>
                                </div>
                            </div>
                            @if($currentBookInfo->book_summary)
                                <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                                    <i class="fas fa-align-left text-purple-500 mr-3 mt-1"></i>
                                    <div>
                                        <span class="font-semibold text-gray-700">{{ __('Summary') }}:</span>
                                        <p class="text-gray-900 ml-2 mt-1">{{ $currentBookInfo->book_summary }}</p>
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-globe text-orange-500 mr-3"></i>
                                <div>
                                    <span class="font-semibold text-gray-700">{{ __('Language') }}:</span>
                                    <span class="text-gray-900 ml-2">{{ $currentBookInfo->language }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">{{ __('No book information available for the selected language.') }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Statistics -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                        {{ __('Statistics') }}
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Available Languages Card -->
                        <div class="bg-blue-500 rounded-xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-bold text-blue-50">
                                    {{ $availableLanguages->count() }}
                                </div>
                                <div class="text-sm text-blue-100">{{ __('Available Languages') }}</div>
                            </div>
                            <i class="fas fa-globe text-3xl text-blue-200"></i>
                        </div>
                    </div>
                    
                        @php
                            $totalTexts = array_sum($processingStats);
                        @endphp
                        <!-- Total Texts Card -->
                        <div class="bg-green-500 rounded-xl p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-3xl font-bold text-green-50">
                                    {{ $totalTexts }}
                                </div>
                                    <div class="text-sm text-green-100">{{ __('Total Texts') }}</div>
                                </div>
                                <i class="fas fa-file-text text-3xl text-green-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Language Selection -->
        @if($availableLanguages->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-globe mr-3 text-purple-600"></i>
                    {{ __('Select Language') }}
                </h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($availableLanguages as $language)
                        <button onclick="changeLanguage('{{ $language }}')" 
                                class="language-tab px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition duration-300 shadow-lg {{ $language === $selectedLanguage ? 'active' : '' }}"
                                data-language="{{ $language }}">
                            <i class="fas fa-language mr-2"></i>
                            {{ $language }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Processing Types -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-list mr-3 text-blue-600"></i>
                {{ __('Processing Results') }}
                    </h3>
                    
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- ملخص -->
                <div class="processing-type-card bg-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-lg {{ $processingStats['summarized'] > 0 ? '' : 'opacity-50' }}"
                     onclick="showProcessingType('summarized')" 
                     data-type="summarized" 
                     data-language="{{ $selectedLanguage }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-compress-alt text-purple-600 text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ __('Summary') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('Summarized Text') }}</p>
                            </div>
                        </div>
                        <div class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ $processingStats['summarized'] }}
                        </div>
                    </div>
                </div>

                <!-- ملخص على شكل نقاط -->
                <div class="processing-type-card bg-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-lg {{ $processingStats['formatting'] > 0 ? '' : 'opacity-50' }}"
                     onclick="showProcessingType('formatting')" 
                     data-type="formatting" 
                     data-language="{{ $selectedLanguage }}">
                    <div class="flex items-center justify-between mb-4">
                                                    <div class="flex items-center">
                            <i class="fas fa-list-ul text-orange-600 text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ __('Bullet Points') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('Bullet Points Summary') }}</p>
                            </div>
                        </div>
                        <div class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ $processingStats['formatting'] }}
                                                    </div>
                                                    </div>
                                                </div>
                                                
                <!-- ترجمة -->
                <div class="processing-type-card bg-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-lg {{ $processingStats['translated'] > 0 ? '' : 'opacity-50' }}"
                     onclick="showProcessingType('translated')" 
                     data-type="translated" 
                     data-language="{{ $selectedLanguage }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-language text-green-600 text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ __('Translation') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('Translated Text') }}</p>
                            </div>
                        </div>
                        <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ $processingStats['translated'] }}
                        </div>
                                                </div>
                                            </div>

                <!-- تحسين النص -->
                <div class="processing-type-card bg-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-lg {{ $processingStats['enhanced'] > 0 ? '' : 'opacity-50' }}"
                     onclick="showProcessingType('enhanced')" 
                     data-type="enhanced" 
                     data-language="{{ $selectedLanguage }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-magic text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ __('Enhanced') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('Enhanced Text') }}</p>
                                    </div>
                                </div>
                        <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ $processingStats['enhanced'] }}
                        </div>
                    </div>
                </div>

                <!-- مقالات المدونة -->
                <div class="processing-type-card bg-white border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:shadow-lg {{ $processingStats['blog_articles'] > 0 ? '' : 'opacity-50' }}"
                     onclick="showProcessingType('blog_articles')" 
                     data-type="blog_articles" 
                     data-language="{{ $selectedLanguage }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-newspaper text-red-600 text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ __('Blog Articles') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('Blog Article') }}</p>
                            </div>
                        </div>
                        <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">
                            {{ $processingStats['blog_articles'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Text Display Area -->
        <div id="text-display-area" class="bg-white rounded-lg shadow-md p-8">
            <div id="default-content" class="text-center py-12">
                <i class="fas fa-hand-point-up text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-600 mb-2">{{ __('Select Processing Type') }}</h3>
                <p class="text-gray-500">{{ __('Click on any processing type above to view the texts') }}</p>
            </div>
            
            <div id="text-content" class="hidden">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 id="content-title" class="text-2xl font-bold text-gray-900"></h3>
                        <p id="content-subtitle" class="text-gray-600"></p>
                    </div>
                    <div class="flex items-center space-x-4 rtl:space-x-reverse">
                        <button id="prev-btn" onclick="showPreviousText()" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-2"></i>
                            {{ __('Previous') }}
                        </button>
                        <span id="text-counter" class="text-sm text-gray-600"></span>
                        <button id="next-btn" onclick="showNextText()" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('Next') }}
                            <i class="fas fa-chevron-right mr-2"></i>
                        </button>
                    </div>
                </div>
                
                <div id="text-display" class="text-display">
                    <!-- Text content will be loaded here -->
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} {{ __('AI Books Manager') }}. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

    <script>
        let currentTexts = [];
        let currentTextIndex = 0;
        let currentType = '';
        let currentLanguage = '{{ $selectedLanguage }}';

        // Language switcher
        document.getElementById('language-switcher').addEventListener('change', function() {
            const language = this.value;
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            url.searchParams.set('lang', language);
            window.location.href = url.toString();
        });

        // Change language
        function changeLanguage(language) {
            currentLanguage = language;
            
            // Update active language tab
            document.querySelectorAll('.language-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update book information for new language
            updateBookInfo(language);
            
            // Update processing type cards with new language
            updateProcessingStats(language);
            
            // Reset text display
            resetTextDisplay();
        }

        // Update book information for new language
        function updateBookInfo(language) {
            console.log('Updating book info for language:', language);
            
            // إضافة loading state
            const bookInfoSection = document.querySelector('.lg\\:col-span-2');
            if (bookInfoSection) {
                const loadingHtml = `
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-4"></i>
                        <p class="text-gray-500">{{ __('Loading book information...') }}</p>
                    </div>
                `;
                bookInfoSection.innerHTML = loadingHtml;
            }
            
            // تحديث معلومات الكتاب حسب اللغة الجديدة مع encoding للغة
            const url = `/books/{{ $book->book_identify }}/info/${encodeURIComponent(language)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Book info response:', data);
                    
                    if (data.success && data.bookInfo) {
                        const bookInfo = data.bookInfo;
                        let fallbackNotice = '';
                        
                        // إضافة إشعار إذا تم استخدام fallback language
                        if (bookInfo.fallback_used) {
                            fallbackNotice = `
                                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                        <span class="text-yellow-800 text-sm">
                                            {{ __('Information not available in') }} "${bookInfo.original_language}". 
                                            {{ __('Showing information in') }} "${bookInfo.language_display || bookInfo.language}" {{ __('instead.') }}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }
                        
                        const bookInfoHtml = `
                            <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                                {{ __('Book Information') }}
                            </h2>
                            
                            ${fallbackNotice}
                            
                            <div class="space-y-4">
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <i class="fas fa-book-open text-blue-500 mr-3"></i>
                                    <div>
                                        <span class="font-semibold text-gray-700">{{ __('Title') }}:</span>
                                        <span class="text-gray-900 ml-2">${bookInfo.title || '{{ __("Not available") }}'}</span>
                                    </div>
                                </div>
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <i class="fas fa-user text-green-500 mr-3"></i>
                                    <div>
                                        <span class="font-semibold text-gray-700">{{ __('Author') }}:</span>
                                        <span class="text-gray-900 ml-2">${bookInfo.author || '{{ __("Not available") }}'}</span>
                                    </div>
                                </div>
                                ${bookInfo.book_summary ? `
                                    <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                                        <i class="fas fa-align-left text-purple-500 mr-3 mt-1"></i>
                                        <div>
                                            <span class="font-semibold text-gray-700">{{ __('Summary') }}:</span>
                                            <p class="text-gray-900 ml-2 mt-1">${bookInfo.book_summary}</p>
                                        </div>
                                    </div>
                                ` : ''}
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <i class="fas fa-globe text-orange-500 mr-3"></i>
                                    <div>
                                        <span class="font-semibold text-gray-700">{{ __('Language') }}:</span>
                                        <span class="text-gray-900 ml-2">${bookInfo.language_display || bookInfo.language}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        if (bookInfoSection) {
                            bookInfoSection.innerHTML = bookInfoHtml;
                        }
                    } else {
                        // عرض رسالة عدم توفر المعلومات مع تفاصيل إضافية
                        let message = data.message || '{{ __("No book information available for the selected language.") }}';
                        
                        if (data.requested_language && data.normalized_language) {
                            message += ` ({{ __('Requested') }}: ${data.requested_language}, {{ __('Normalized') }}: ${data.normalized_language})`;
                        }
                        
                        const noInfoHtml = `
                            <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                                {{ __('Book Information') }}
                            </h2>
                            
                            <div class="text-center py-8">
                                <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">${message}</p>
                                <div class="mt-4">
                                    <button onclick="tryFallbackLanguage()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('Try Default Language') }}
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        if (bookInfoSection) {
                            bookInfoSection.innerHTML = noInfoHtml;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating book info:', error);
                    
                    // عرض رسالة خطأ مع خيار إعادة المحاولة
                    const errorHtml = `
                        <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-info-circle mr-3 text-blue-600"></i>
                            {{ __('Book Information') }}
                        </h2>
                        
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                            <p class="text-red-500">{{ __('Error loading book information.') }}</p>
                            <p class="text-gray-500 text-sm mt-2">${error.message}</p>
                            <div class="mt-4">
                                <button onclick="retryBookInfo()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                                    {{ __('Retry') }}
                                </button>
                                <button onclick="tryFallbackLanguage()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    {{ __('Try Default Language') }}
                                </button>
                            </div>
                        </div>
                    `;
                    
                    if (bookInfoSection) {
                        bookInfoSection.innerHTML = errorHtml;
                    }
                });
        }

        // Update processing stats for new language
        function updateProcessingStats(language) {
            // إضافة loading state
            const processingCards = document.querySelectorAll('.processing-type-card');
            processingCards.forEach(card => {
                const countElement = card.querySelector('.bg-purple-100, .bg-orange-100, .bg-green-100, .bg-blue-100, .bg-red-100');
                if (countElement) {
                    countElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }
            });
            
            const url = `/books/{{ $book->book_identify }}/stats/${language}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.stats;
                        
                        // Update display for each processing type
                        Object.keys(stats).forEach(type => {
                            const card = document.querySelector(`[data-type="${type}"]`);
                            if (card) {
                                // Find the count element (badge)
                                const countElement = card.querySelector('.bg-purple-100, .bg-orange-100, .bg-green-100, .bg-blue-100, .bg-red-100');
                                if (countElement) {
                                    countElement.textContent = stats[type];
                                    
                                    // Update opacity based on count
                                    if (stats[type] === 0) {
                                        card.classList.add('opacity-50');
                                    } else {
                                        card.classList.remove('opacity-50');
                                    }
                                    
                                    // Update data-language attribute
                                    card.setAttribute('data-language', language);
                                }
                            }
                        });
                        
                        console.log('Stats updated for language:', language, stats);
                    } else {
                        console.error('Error updating stats:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                    // إعادة تعيين القيم الأصلية في حالة الخطأ
                    processingCards.forEach(card => {
                        const countElement = card.querySelector('.bg-purple-100, .bg-orange-100, .bg-green-100, .bg-blue-100, .bg-red-100');
                        if (countElement) {
                            countElement.textContent = '0';
                        }
                    });
                });
        }

        // Get color class for processing type
        function getColorClass(type) {
            const colors = {
                'summarized': 'purple',
                'formatting': 'orange',
                'translated': 'green',
                'enhanced': 'blue',
                'blog_articles': 'red'
            };
            return colors[type] || 'gray';
        }

        // Show processing type
        function showProcessingType(type) {
            currentType = type;
            
            // Update active card
            document.querySelectorAll('.processing-type-card').forEach(card => {
                card.classList.remove('active');
            });
            event.target.closest('.processing-type-card').classList.add('active');
            
            // Load texts using current language
            loadTexts(type, currentLanguage);
        }

        // Load texts for specific type and language
        function loadTexts(type, language) {
            const url = `/books/{{ $book->book_identify }}/texts/${type}/${language}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentTexts = data.texts;
                        currentTextIndex = 0;
                        
                        if (currentTexts.length > 0) {
                            showText(0);
                        } else {
                            showNoTextsMessage();
                        }
                    } else {
                        showErrorMessage(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading texts:', error);
                    showErrorMessage('Error loading texts');
                });
        }

        // Show text at specific index
        function showText(index) {
            if (index < 0 || index >= currentTexts.length) return;
            
            currentTextIndex = index;
            const text = currentTexts[index];
            
            // Update navigation buttons
            updateNavigationButtons();
            
            // Update counter
            document.getElementById('text-counter').textContent = `${index + 1} / ${currentTexts.length}`;
            
            // Update title
            const typeNames = {
                'summarized': '{{ __("Summary") }}',
                'formatting': '{{ __("Bullet Points Summary") }}',
                'translated': '{{ __("Translation") }}',
                'enhanced': '{{ __("Enhanced Text") }}',
                'blog_articles': '{{ __("Blog Articles") }}'
            };
            
            document.getElementById('content-title').textContent = typeNames[currentType];
            document.getElementById('content-subtitle').textContent = `${currentLanguage} - ${text.created_at}`;
            
            // Update text content
            const textDisplay = document.getElementById('text-display');
            textDisplay.innerHTML = '';
            
            // Add title if exists
            if (text.title) {
                const titleElement = document.createElement('h4');
                titleElement.className = 'text-xl font-bold text-gray-900 mb-4';
                titleElement.textContent = text.title;
                textDisplay.appendChild(titleElement);
            }
            
            // Add content
            const contentElement = document.createElement('div');
            contentElement.className = 'text-content bg-gray-50 rounded-lg p-6 max-h-96 overflow-y-auto';
            contentElement.textContent = text.content;
            textDisplay.appendChild(contentElement);
            
            // Show text display area
            document.getElementById('default-content').classList.add('hidden');
            document.getElementById('text-content').classList.remove('hidden');
            
            // Add fade-in animation
            textDisplay.classList.add('fade-in');
            setTimeout(() => textDisplay.classList.remove('fade-in'), 500);
        }

        // Update navigation buttons
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            
            prevBtn.disabled = currentTextIndex === 0;
            nextBtn.disabled = currentTextIndex === currentTexts.length - 1;
            
            if (prevBtn.disabled) {
                prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
            
            if (nextBtn.disabled) {
                nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // Show previous text
        function showPreviousText() {
            if (currentTextIndex > 0) {
                showText(currentTextIndex - 1);
            }
        }

        // Show next text
        function showNextText() {
            if (currentTextIndex < currentTexts.length - 1) {
                showText(currentTextIndex + 1);
            }
        }

        // Show no texts message
        function showNoTextsMessage() {
            document.getElementById('default-content').classList.add('hidden');
            document.getElementById('text-content').classList.remove('hidden');
            
            document.getElementById('content-title').textContent = '{{ __("No Texts Available") }}';
            document.getElementById('content-subtitle').textContent = '';
            document.getElementById('text-display').innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">{{ __('No texts available for this processing type and language.') }}</p>
                </div>
            `;
            
            // Hide navigation buttons
            document.getElementById('prev-btn').style.display = 'none';
            document.getElementById('next-btn').style.display = 'none';
            document.getElementById('text-counter').style.display = 'none';
        }

        // Show error message
        function showErrorMessage(message) {
            document.getElementById('default-content').classList.add('hidden');
            document.getElementById('text-content').classList.remove('hidden');
            
            document.getElementById('content-title').textContent = '{{ __("Error") }}';
            document.getElementById('content-subtitle').textContent = '';
            document.getElementById('text-display').innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-red-500">${message}</p>
                </div>
            `;
        }

        // Reset text display
        function resetTextDisplay() {
            document.getElementById('default-content').classList.remove('hidden');
            document.getElementById('text-content').classList.add('hidden');
            currentTexts = [];
            currentTextIndex = 0;
            currentType = '';
            
            // Remove active class from all processing type cards
            document.querySelectorAll('.processing-type-card').forEach(card => {
                card.classList.remove('active');
            });
        }

        // Show login message
        function showLoginMessage() {
            alert('{{ __('Please login to access this feature.') }}');
        }

        // Function to retry loading book info
        function retryBookInfo() {
            if (currentLanguage) {
                updateBookInfo(currentLanguage);
            }
        }

        // Function to try fallback language
        function tryFallbackLanguage() {
            const fallbackLanguage = '{{ app()->getLocale() === "ar" ? "العربية" : "English" }}';
            updateBookInfo(fallbackLanguage);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Show first language by default if available
            const firstLanguage = document.querySelector('.language-tab');
            if (firstLanguage) {
                firstLanguage.click();
            }
        });
    </script>
</body>
</html> 