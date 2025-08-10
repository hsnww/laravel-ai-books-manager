<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookInfo;
use App\Services\BookInfoService;
use App\Helpers\LanguageHelper;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $bookInfoService;

    public function __construct(BookInfoService $bookInfoService)
    {
        $this->bookInfoService = $bookInfoService;
    }

    /**
     * Show the home page with latest books
     */
    public function index()
    {
        // Get latest 9 books with their info
        $books = Book::with(['bookInfos', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(9)
            ->get();

        // Get books with preferred info based on browser language
        $books = $this->bookInfoService->getBooksWithPreferredInfo($books);

        // Get statistics
        $totalFiles = \App\Models\FileManager::count();
        $supportedLanguages = 16; // Number of supported languages
        $processingTypes = 5; // Number of processing types

        return view('welcome', compact('books', 'totalFiles', 'supportedLanguages', 'processingTypes'));
    }

    /**
     * Show all books with pagination
     */
    public function books(Request $request)
    {
        // Set language based on request or browser
        $locale = $request->get('lang', $request->getLocale());
        if (LanguageHelper::isValidLanguage($locale)) {
            LanguageHelper::setLanguage($locale);
        }

        // Get books with pagination
        $books = Book::with(['bookInfos', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(12); // 12 books per page (3 cards × 4 rows)

        // Get books with preferred info based on browser language using BookInfoService
        $books->getCollection()->transform(function ($book) {
            $book->preferred_book_info = $this->bookInfoService->getBookWithPreferredInfo($book);
            return $book;
        });

        return view('books.index', compact('books'));
    }
} 