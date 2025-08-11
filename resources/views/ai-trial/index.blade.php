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
            
            <!-- Email Verification Notice -->
            @if(!auth()->user()->hasVerifiedEmail())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-1 mr-3"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">{{ __('Email Verification Required') }}</h3>
                            <p class="text-yellow-700 mb-4">
                                {{ __('To access AI Trial features, you must verify your email address first.') }}
                            </p>
                            <div class="flex space-x-4 rtl:space-x-reverse">
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        {{ __('Resend Verification Email') }}
                                    </button>
                                </form>
                                <a href="{{ route('verification.notice') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                    <i class="fas fa-envelope mr-2"></i>
                                    {{ __('Verify Email') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
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
            @if(auth()->user()->hasVerifiedEmail())
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
                        
                        <!-- Processing Type Selection -->
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
                                <option value="">{{ __('Select processing type') }}</option>
                                <option value="extract_info">{{ __('Extract Information') }}</option>
                                <option value="summarize">{{ __('Summarize Text') }}</option>
                                <option value="translate">{{ __('Translate Text') }}</option>
                                <option value="enhance">{{ __('Enhance Text') }}</option>
                                <option value="improve_format">{{ __('Improve Formatting') }}</option>
                            </select>
                        </div>
                        
                        <!-- Language Selection -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Target Language') }}
                            </label>
                            <select 
                                id="language" 
                                name="language" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                                <option value="">{{ __('Select language') }}</option>
                                <option value="arabic">{{ __('Arabic') }}</option>
                                <option value="english">{{ __('English') }}</option>
                            </select>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between">
                            <button 
                                type="button" 
                                id="clearText"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200"
                            >
                                <i class="fas fa-eraser mr-2"></i>
                                {{ __('Clear Text') }}
                            </button>
                            
                            <button 
                                type="submit" 
                                id="processBtn"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 flex items-center"
                            >
                                <span id="processBtnText">
                                    <i class="fas fa-play mr-2"></i>
                                    {{ __('Start Processing') }}
                                </span>
                                <i id="loadingSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Results Section -->
            @if(auth()->user()->hasVerifiedEmail())
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
            @endif
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
            @if(auth()->user()->hasVerifiedEmail())
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
                const textToCopy = resultContent.textContent;
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // تغيير النص مؤقتاً
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check mr-2"></i>{{ __("Copied!") }}';
                    copyBtn.classList.add('bg-green-700');
                    
                    setTimeout(function() {
                        copyBtn.innerHTML = originalText;
                        copyBtn.classList.remove('bg-green-700');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Could not copy text: ', err);
                    alert('{{ __("Could not copy text to clipboard") }}');
                });
            });
            @endif
        });
    </script>
</body>
</html> 