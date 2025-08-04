<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('AI Books Manager') }}</title>

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
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-brain text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('AI Books Manager') }}
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <!-- Language Switcher -->
                    <div class="relative">
                        <select id="language-switcher" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('Welcome to AI Books Manager') }}
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('An intelligent system for managing, processing, and analyzing books using artificial intelligence. Extract text from PDFs, enhance content, translate, summarize, and more.') }}
            </p>
        </div>

        <!-- Features Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="text-center">
                    <i class="fas fa-file-pdf text-red-500 text-3xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">{{ __('PDF Processing') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ __('Extract text from PDF files with high accuracy and maintain original formatting.') }}
                    </p>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="text-center">
                    <i class="fas fa-brain text-blue-500 text-3xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">{{ __('AI Processing') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ __('Enhance, translate, summarize, and improve text using advanced AI algorithms.') }}
                    </p>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="text-center">
                    <i class="fas fa-language text-green-500 text-3xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Multi-Language') }}</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ __('Support for multiple languages including Arabic, English, and many others.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Books Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-books mr-2"></i>
                    {{ __('Recent Books') }}
                </h3>
                @auth
                    <a href="{{ route('filament.admin.pages.dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('Add New Book') }}
                    </a>
                @endauth
            </div>

            @if($books->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($books as $book)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <i class="fas fa-book text-blue-500 mr-2"></i>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $book->book_identify }}
                                    </h4>
                                </div>
                                <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                                    {{ $book->book_language }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                <p><i class="fas fa-calendar mr-1"></i> {{ $book->created_at->format('Y-m-d H:i') }}</p>
                            </div>

                            @if($book->bookInfo)
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-1">
                                        {{ $book->bookInfo->title }}
                                    </h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                        {{ __('Author') }}: {{ $book->bookInfo->author }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ Str::limit($book->bookInfo->book_summary, 100) }}
                                    </p>
                                </div>
                            @endif

                            <div class="mt-4 flex space-x-2 rtl:space-x-reverse">
                                <a href="{{ route('books.show', $book->book_identify) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition duration-200">
                                    <i class="fas fa-eye mr-1"></i>
                                    {{ __('View Book') }}
                                </a>
                                @auth
                                    <a href="{{ route('ai-processor.show', $book->book_identify) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition duration-200">
                                        <i class="fas fa-cogs mr-1"></i>
                                        {{ __('Process') }}
                                    </a>
                                    <a href="{{ route('file-manager.show', $book->book_identify) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition duration-200">
                                        <i class="fas fa-folder mr-1"></i>
                                        {{ __('Files') }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-books text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('No books available yet.') }}</p>
                    @auth
                        <a href="{{ route('filament.admin.pages.dashboard') }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            {{ __('Upload Your First Book') }}
                        </a>
                    @endauth
                </div>
            @endif
        </div>

        <!-- Statistics Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-books text-blue-500 text-2xl mb-2"></i>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $books->count() }}</h4>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Total Books') }}</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-file-alt text-green-500 text-2xl mb-2"></i>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalFiles }}</h4>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Processed Files') }}</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-language text-purple-500 text-2xl mb-2"></i>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $supportedLanguages }}</h4>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Supported Languages') }}</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center">
                <i class="fas fa-cogs text-orange-500 text-2xl mb-2"></i>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $processingTypes }}</h4>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Processing Types') }}</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-600 dark:text-gray-300">
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
    </script>
    </body>
</html>
