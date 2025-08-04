<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('AI Books Manager') }} - {{ __('AI Trial') }}</title>

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
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <!-- Language Switcher -->
                    <div class="relative">
                        <select id="language-switcher" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                            <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>
                    
                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <nav class="flex items-center space-x-4 rtl:space-x-reverse">
                            @auth
                                <a href="{{ route('filament.admin.pages.dashboard') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    {{ __('Dashboard') }}
                                </a>
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
        <div class="max-w-4xl mx-auto">
            <!-- Page Title -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-robot text-blue-600 mr-3"></i>
                    {{ __('AI Trial') }}
                </h2>
                <p class="text-gray-600">{{ __('Test AI capabilities with your own text') }}</p>
            </div>
            
            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">{{ __('Trial Information') }}</h3>
                        <ul class="text-blue-700 space-y-1">
                            <li>• {{ __('Maximum text length: 5000 characters') }}</li>
                            <li>• {{ __('Minimum text length: 50 characters') }}</li>
                            <li>• {{ __('Results are temporary and not saved') }}</li>
                            <li>• {{ __('You can try all processing types') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Processing Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-6">
                    <i class="fas fa-cogs text-green-600 mr-2"></i>
                    {{ __('Processing Text') }}
                </h2>

                <form id="aiTrialForm" class="space-y-6">
                    @csrf
                    
                    <!-- Text Input -->
                    <div>
                        <label for="text" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Text to Process') }}
                        </label>
                        <div class="relative">
                            <textarea 
                                id="text" 
                                name="text" 
                                rows="8" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                placeholder="{{ __('Enter text here (50-5000 characters)...') }}"
                                maxlength="5000"
                                required
                            ></textarea>
                            <div class="absolute bottom-2 left-2 text-xs text-gray-500">
                                <span id="charCount">0</span> / 5000
                            </div>
                        </div>
                    </div>

                    <!-- Processing Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Processing Type -->
                        <div>
                            <label for="processing_type" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Processing Type') }}
                            </label>
                            <select 
                                id="processing_type" 
                                name="processing_type" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">{{ __('Select Processing Type') }}</option>
                                <option value="extract_info">{{ __('Book Information Extraction') }}</option>
                                <option value="summarize">{{ __('Text Summarization') }}</option>
                                <option value="translate">{{ __('Text Translation') }}</option>
                                <option value="enhance">{{ __('Text Enhancement') }}</option>
                                <option value="improve_format">{{ __('Bullet Points Summary') }}</option>
                            </select>
                        </div>

                        <!-- Language -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Language') }}
                            </label>
                            <select 
                                id="language" 
                                name="language" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">{{ __('Select Language') }}</option>
                                <option value="Arabic">العربية</option>
                                <option value="English">الإنجليزية</option>
                                <option value="French">الفرنسية</option>
                                <option value="Spanish">الإسبانية</option>
                                <option value="German">الألمانية</option>
                                <option value="Italian">الإيطالية</option>
                                <option value="Portuguese">البرتغالية</option>
                                <option value="Russian">الروسية</option>
                                <option value="Chinese">الصينية</option>
                                <option value="Japanese">اليابانية</option>
                                <option value="Korean">الكورية</option>
                                <option value="Turkish">التركية</option>
                                <option value="Persian">الفارسية</option>
                                <option value="Urdu">الأردية</option>
                                <option value="Hindi">الهندية</option>
                                <option value="Bengali">البنغالية</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-3 rtl:space-x-reverse">
                            <button 
                                type="button" 
                                id="clearText" 
                                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200"
                            >
                                <i class="fas fa-eraser mr-2"></i>
                                {{ __('Clear Text') }}
                            </button>
                        </div>
                        
                        <button 
                            type="submit" 
                            id="processBtn" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 flex items-center"
                        >
                            <i class="fas fa-magic mr-2"></i>
                            <span id="processBtnText">{{ __('Start Processing') }}</span>
                            <div id="loadingSpinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" class="bg-white rounded-lg shadow-md p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-chart-line text-green-600 mr-2"></i>
                        {{ __('Result') }}
                    </h3>
                    <button 
                        id="copyResult" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200"
                    >
                        <i class="fas fa-copy mr-2"></i>
                        {{ __('Copy Result') }}
                    </button>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div id="resultContent" class="text-gray-800 whitespace-pre-wrap"></div>
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
        // Language switcher
        document.getElementById('language-switcher').addEventListener('change', function() {
            const language = this.value;
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            url.searchParams.set('lang', language);
            window.location.href = url.toString();
        });

        // AI Trial functionality
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('aiTrialForm');
            const textArea = document.getElementById('text');
            const charCount = document.getElementById('charCount');
            const clearBtn = document.getElementById('clearText');
            const processBtn = document.getElementById('processBtn');
            const processBtnText = document.getElementById('processBtnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const resultsSection = document.getElementById('resultsSection');
            const resultContent = document.getElementById('resultContent');
            const copyBtn = document.getElementById('copyResult');

            // عداد الحروف
            function updateCharCount() {
                const count = textArea.value.length;
                charCount.textContent = count;
                
                if (count > 4500) {
                    charCount.classList.add('text-red-500');
                } else {
                    charCount.classList.remove('text-red-500');
                }
            }

            textArea.addEventListener('input', updateCharCount);

            // مسح النص
            clearBtn.addEventListener('click', function() {
                textArea.value = '';
                updateCharCount();
                resultsSection.classList.add('hidden');
            });

            // معالجة النموذج
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // إظهار loading
                processBtn.disabled = true;
                processBtnText.textContent = '{{ __("Processing...") }}';
                loadingSpinner.classList.remove('hidden');
                
                // إخفاء النتائج السابقة
                resultsSection.classList.add('hidden');
                
                fetch('{{ route("ai-trial.process") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultContent.textContent = data.result;
                        resultsSection.classList.remove('hidden');
                        
                        // تمرير إلى أعلى الصفحة
                        resultsSection.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('{{ __("Error occurred while processing text") }}: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __("Error occurred while processing text") }}');
                })
                .finally(() => {
                    // إخفاء loading
                    processBtn.disabled = false;
                    processBtnText.textContent = '{{ __("Start Processing") }}';
                    loadingSpinner.classList.add('hidden');
                });
            });

            // نسخ النتيجة
            copyBtn.addEventListener('click', function() {
                const text = resultContent.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    // تغيير نص الزر مؤقتاً
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check mr-2"></i>{{ __("Copied!") }}';
                    copyBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    copyBtn.classList.add('bg-green-500');
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                        copyBtn.classList.remove('bg-green-500');
                        copyBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    }, 2000);
                }).catch(err => {
                    console.error('{{ __("Failed to copy text") }}: ', err);
                    alert('{{ __("Failed to copy text") }}');
                });
            });
        });
    </script>
</body>
</html> 