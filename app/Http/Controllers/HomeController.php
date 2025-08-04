<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class HomeController extends Controller
{
    /**
     * Show the home page
     */
    public function index(Request $request)
    {
        // Set language based on request or browser
        $locale = $request->get('lang', $request->getLocale());
        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
        }

        // Get recent books with their info
        $books = Book::with('bookInfo')
            ->orderBy('created_at', 'desc')
            ->limit(9)
            ->get();

        // Get statistics
        $totalFiles = FileManager::count();
        $supportedLanguages = 16; // Number of supported languages
        $processingTypes = 5; // Number of processing types

        return view('welcome', compact('books', 'totalFiles', 'supportedLanguages', 'processingTypes'));
    }
} 