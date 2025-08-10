<?php

namespace App\Http\Controllers;

use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FileManagerController extends Controller
{
    private $fileManagerService;
    
    public function __construct(FileManagerService $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }
    
    /**
     * عرض صفحة إدارة الملفات
     */
    public function show($bookId)
    {
        $files = $this->fileManagerService->getBookFiles($bookId);
        
        // Get book information from books_info table
        $bookInfo = \App\Models\BookInfo::join('books', 'books_info.book_id', '=', 'books.id')
            ->where('books.book_identify', $bookId)
            ->select('books_info.title', 'books_info.author', 'books_info.language')
            ->first();
        
        // إذا لم يتم العثور على معلومات، جرب الحصول على معلومات باللغة الافتراضية
        if (!$bookInfo) {
            $book = \App\Models\Book::where('book_identify', $bookId)->first();
            if ($book) {
                $bookInfo = $book->getBookInfoByLanguage();
            }
        }
        
        return view('file-manager.show', compact('files', 'bookId', 'bookInfo'));
    }
    
    /**
     * تعديل ملف نصي
     */
    public function editFile(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|string',
            'filename' => 'required|string',
            'content' => 'required|string'
        ]);
        
        $result = $this->fileManagerService->editFile(
            $request->book_id,
            $request->filename,
            $request->content
        );
        
        return response()->json($result);
    }
    
    /**
     * تقسيم ملف نصي
     */
    public function splitFile(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|string',
            'filename' => 'required|string',
            'marker' => 'nullable|string'
        ]);
        
        $result = $this->fileManagerService->splitFile(
            $request->book_id,
            $request->filename,
            $request->marker ?? '#####'
        );
        
        return response()->json($result);
    }
    
    /**
     * دمج ملفات نصية
     */
    public function mergeFiles(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|string',
            'files_to_merge' => 'required|array',
            'new_title' => 'required|string'
        ]);
        
        $result = $this->fileManagerService->mergeFiles(
            $request->book_id,
            $request->files_to_merge,
            $request->new_title
        );
        
        return response()->json($result);
    }
    
    /**
     * إعادة ترتيب الفصول
     */
    public function reorderChapters(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|string',
            'reorder_data' => 'required|array'
        ]);
        
        $result = $this->fileManagerService->reorderChapters(
            $request->book_id,
            $request->reorder_data
        );
        
        return response()->json($result);
    }
    
    /**
     * حذف متعدد للملفات
     */
    public function deleteMultipleFiles(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|string',
            'filenames' => 'required|array'
        ]);
        
        $result = $this->fileManagerService->deleteMultipleFiles(
            $request->book_id,
            $request->filenames
        );
        
        return response()->json($result);
    }
    
    /**
     * الحصول على قائمة ملفات الكتاب
     */
    public function getBookFiles($bookId): JsonResponse
    {
        $files = $this->fileManagerService->getBookFiles($bookId);
        
        return response()->json([
            'success' => true,
            'files' => $files
        ]);
    }
} 