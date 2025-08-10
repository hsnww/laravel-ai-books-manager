<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Helpers\LanguageHelper::getTextDirection() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('All Books') }} - {{ __('AI Books Manager') }}</title>

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
        .book-card { transition: all 0.3s ease; }
        .book-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; }
        .pagination .page-item { list-style: none; }
        .pagination .page-link { 
            padding: 0.5rem 1rem; 
            border: 1px solid #e5e7eb; 
            border-radius: 0.375rem; 
            text-decoration: none; 
            color: #374151; 
            transition: all 0.2s; 
        }
        .pagination .page-link:hover { background-color: #f3f4f6; }
        .pagination .page-item.active .page-link { 
            background-color: #3b82f6; 
            color: white; 
            border-color: #3b82f6; 
        }
        .pagination .page-item.disabled .page-link { 
            color: #9ca3af; 
            pointer-events: none; 
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
                        <p class="text-sm text-gray-600">{{ __('All Books') }}</p>
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
                            @auth
                                <a href="{{ route('filament.admin.pages.dashboard') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    {{ __('Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
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
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('All Books') }}</h2>
            <p class="text-gray-600">{{ __('Browse all available books in the system') }}</p>
        </div>

        <!-- Books Grid -->
        @if($books->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($books as $book)
                    <div class="book-card bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-book text-blue-600 text-2xl mr-3"></i>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">{{ $book->book_identify }}</h3>
                                        <p class="text-sm text-gray-600">{{ __('Book ID') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $book->book_language ?? 'Unknown' }}
                                    </span>
                                </div>
                            </div>

                            @if($book->bookInfos && $book->bookInfos->count() > 0)
                                @php
                                    // الحصول على معلومات الكتاب حسب لغة المتصفح
                                    $currentLocale = \App\Helpers\LanguageHelper::getCurrentLanguage();
                                    $preferredLanguage = $currentLocale === 'ar' ? 'Arabic' : 'English';
                                    
                                    // البحث عن معلومات الكتاب باللغة المفضلة
                                    $bookInfo = $book->bookInfos->where('language', $preferredLanguage)->first();
                                    
                                    // إذا لم يتم العثور على معلومات باللغة المفضلة، استخدم أول مدخل متوفر
                                    if (!$bookInfo) {
                                        $bookInfo = $book->bookInfos->first();
                                    }
                                @endphp
                                
                                @if($bookInfo)
                                    <div class="space-y-2 mb-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ __('Title') }}:</h4>
                                            <p class="text-sm text-gray-600">{{ $bookInfo->title }}</p>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ __('Author') }}:</h4>
                                            <p class="text-sm text-gray-600">{{ $bookInfo->author }}</p>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ __('Language') }}:</h4>
                                            <p class="text-sm text-gray-600">{{ $bookInfo->language }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $book->created_at->format('M d, Y') }}
                                </span>
                                @if($book->user)
                                    <span>
                                        <i class="fas fa-user mr-1"></i>
                                        {{ $book->user->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex space-x-2 rtl:space-x-reverse">
                                <a href="{{ route('books.show', $book->book_identify) }}" 
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-eye mr-2"></i>
                                    {{ __('View') }}
                                </a>
                                @auth
                                    <a href="{{ route('ai-processor.show', $book->book_identify) }}" 
                                       class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition duration-200">
                                        <i class="fas fa-cogs mr-2"></i>
                                        {{ __('Process') }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($books->hasPages())
                <div class="flex justify-center">
                    <nav class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($books->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a href="{{ $books->previousPageUrl() }}" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($books->getUrlRange(1, $books->lastPage()) as $page => $url)
                            @if ($page == $books->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($books->hasMorePages())
                            <li class="page-item">
                                <a href="{{ $books->nextPageUrl() }}" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </nav>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-books text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-600 mb-2">{{ __('No Books Found') }}</h3>
                <p class="text-gray-500">{{ __('No books have been uploaded yet.') }}</p>
            </div>
        @endif
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
    </script>
</body>
</html>
