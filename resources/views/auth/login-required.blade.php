<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Authentication Required') }} - {{ __('AI Books Manager') }}</title>

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
                        <p class="text-sm text-gray-600">{{ __('Authentication Required') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <!-- Icon -->
            <div class="mb-8">
                <i class="fas fa-lock text-red-500 text-6xl mb-4"></i>
            </div>
            
            <!-- Title -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                {{ __('Authentication Required') }}
            </h1>
            
            <!-- Description -->
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                {{ __('This page requires authentication. Please log in to access the AI processing and file management features.') }}
            </p>
            
            <!-- Features List -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    {{ __('Available Features After Login') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start p-4 bg-green-50 rounded-lg">
                        <i class="fas fa-cogs text-green-600 text-2xl mr-4 mt-1"></i>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                {{ __('AI Text Processing') }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ __('Enhance, translate, summarize, and format text using artificial intelligence.') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-purple-50 rounded-lg">
                        <i class="fas fa-folder text-purple-600 text-2xl mr-4 mt-1"></i>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                {{ __('File Management') }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ __('Edit, split, merge, and organize text files efficiently.') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                        <i class="fas fa-tachometer-alt text-blue-600 text-2xl mr-4 mt-1"></i>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                {{ __('Admin Dashboard') }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ __('Access comprehensive admin panel for managing books and texts.') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 bg-orange-50 rounded-lg">
                        <i class="fas fa-history text-orange-600 text-2xl mr-4 mt-1"></i>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                {{ __('Processing History') }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ __('Track and review all AI processing activities and results.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 font-semibold">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    {{ __('Login') }}
                </a>
                
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 font-semibold">
                        <i class="fas fa-user-plus mr-2"></i>
                        {{ __('Register') }}
                    </a>
                @endif
                
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center px-8 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200 font-semibold">
                    <i class="fas fa-home mr-2"></i>
                    {{ __('Back to Home') }}
                </a>
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
</body>
</html> 