<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\BookInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookControllerLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $book;
    protected $bookInfoArabic;
    protected $bookInfoEnglish;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user
        $this->user = User::factory()->create();
        
        // Create book
        $this->book = Book::create([
            'book_identify' => 'test-book-123',
            'title' => 'Test Book',
            'file_path' => '/test/path.pdf'
        ]);
        
        // Create book info in Arabic
        $this->bookInfoArabic = BookInfo::create([
            'book_id' => $this->book->id,
            'title' => 'كتاب تجريبي',
            'author' => 'مؤلف تجريبي',
            'book_summary' => 'ملخص تجريبي للكتاب',
            'language' => 'Arabic'
        ]);
        
        // Create book info in English
        $this->bookInfoEnglish = BookInfo::create([
            'book_id' => $this->book->id,
            'title' => 'Test Book',
            'author' => 'Test Author',
            'book_summary' => 'Test book summary',
            'language' => 'English'
        ]);
    }

    /**
     * Test getting book info with Arabic language
     */
    public function test_get_book_info_with_arabic_language()
    {
        $response = $this->get("/books/{$this->book->book_identify}/info/العربية");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'bookInfo' => [
                        'title' => 'كتاب تجريبي',
                        'author' => 'مؤلف تجريبي',
                        'language' => 'Arabic'
                    ]
                ]);
    }

    /**
     * Test getting book info with English language
     */
    public function test_get_book_info_with_english_language()
    {
        $response = $this->get("/books/{$this->book->book_identify}/info/English");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'bookInfo' => [
                        'title' => 'Test Book',
                        'author' => 'Test Author',
                        'language' => 'English'
                    ]
                ]);
    }

    /**
     * Test getting book info with non-existent language (should use fallback)
     */
    public function test_get_book_info_with_non_existent_language_uses_fallback()
    {
        $response = $this->get("/books/{$this->book->book_identify}/info/French");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'bookInfo' => [
                        'fallback_used' => true
                    ]
                ]);
    }

    /**
     * Test getting book info with invalid book identify
     */
    public function test_get_book_info_with_invalid_book_identify()
    {
        $response = $this->get("/books/invalid-book/info/Arabic");
        
        $response->assertStatus(404);
    }
}
