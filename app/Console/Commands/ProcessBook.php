<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BookProcessor;
use App\Models\FileManager;
use Illuminate\Support\Facades\Storage;

class ProcessBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'book:process {file_id : File Manager ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a book from PDF file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileId = $this->argument('file_id');
        
        // البحث عن الملف
        $file = FileManager::find($fileId);
        
        if (!$file) {
            $this->error("File not found with ID: {$fileId}");
            return 1;
        }
        
        $this->info("Processing book: {$file->name}");
        $this->info("File path: {$file->path}");
        $this->info("File size: " . number_format($file->size / 1024 / 1024, 2) . " MB");
        
        // إنشاء خدمة معالجة الكتب
        $processor = new BookProcessor(app(PdfTextExtractor::class));
        
        try {
            $this->info("Starting book processing...");
            
            // معالجة الكتاب
            $result = $processor->processBook($file);
            
            if ($result['success']) {
                $this->info("✅ Book processing successful!");
                $this->info("📚 Book ID: " . $result['book_id']);
                $this->info("📖 Book Info ID: " . $result['book_info_id']);
                $this->info("📄 Pages: " . $result['pages_count']);
                $this->info("🌐 Language: " . $result['language']);
                $this->info("⏱️ Time: " . $result['extraction_time'] . " seconds");
                $this->info("📝 Text length: " . $result['extracted_text_length'] . " characters");
                
                // عرض معلومات الكتاب
                $book = \App\Models\Book::find($result['book_id']);
                $bookInfo = $book->bookInfo()->where('language', $result['language'])->first();
                
                if ($bookInfo) {
                    $this->info("\n📖 Book Information:");
                    $this->info("Title: " . $bookInfo->title);
                    $this->info("Author: " . $bookInfo->author);
                    $this->info("Summary: " . substr($bookInfo->book_summary, 0, 200) . "...");
                }
                
            } else {
                $this->error("❌ Book processing failed: " . $result['error']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
