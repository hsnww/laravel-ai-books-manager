<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\PdfViewerController;
use App\Http\Controllers\AiProcessorController;
use App\Http\Controllers\AiTrialController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Book Routes
Route::prefix('books')->group(function () {
    Route::get('/{bookIdentify}', [App\Http\Controllers\BookController::class, 'show'])->name('books.show');
    Route::get('/{bookIdentify}/text/{textId}/{type}', [App\Http\Controllers\BookController::class, 'getProcessedText'])->name('books.text');
    Route::get('/{bookIdentify}/texts/{type}/{language}', [App\Http\Controllers\BookController::class, 'getTextsByType'])->name('books.texts-by-type');
    Route::get('/{bookIdentify}/stats/{language}', [App\Http\Controllers\BookController::class, 'getProcessingStatsByLanguage'])->name('books.stats-by-language');
});

// File Manager Routes - Protected by authentication
Route::prefix('file-manager')->middleware('auth')->group(function () {
    Route::get('/{bookId}', [FileManagerController::class, 'show'])->name('file-manager.show');
    Route::post('/edit-file', [FileManagerController::class, 'editFile'])->name('file-manager.edit-file');
    Route::post('/split-file', [FileManagerController::class, 'splitFile'])->name('file-manager.split-file');
    Route::post('/merge-files', [FileManagerController::class, 'mergeFiles'])->name('file-manager.merge-files');
    Route::post('/reorder-chapters', [FileManagerController::class, 'reorderChapters'])->name('file-manager.reorder-chapters');
    Route::post('/delete-multiple', [FileManagerController::class, 'deleteMultipleFiles'])->name('file-manager.delete-multiple');
    Route::get('/{bookId}/files', [FileManagerController::class, 'getBookFiles'])->name('file-manager.get-files');
});

// PDF Viewer Routes
Route::prefix('pdf-viewer')->group(function () {
    Route::get('/{id}', [PdfViewerController::class, 'show'])->name('pdf-viewer.show');
    Route::get('/{id}/download', [PdfViewerController::class, 'download'])->name('pdf-viewer.download');
});

// AI Processor Routes - Protected by authentication
Route::prefix('ai-processor')->middleware('auth')->group(function () {
    Route::get('/{bookId}', [AiProcessorController::class, 'show'])->name('ai-processor.show');
    Route::post('/process-files', [AiProcessorController::class, 'processFiles'])->name('ai-processor.process');
    Route::get('/{bookId}/history', [AiProcessorController::class, 'getProcessingHistory'])->name('ai-processor.history');
    Route::get('/{bookId}/processed-texts', [AiProcessorController::class, 'getProcessedTexts'])->name('ai-processor.processed-texts');
});

// AI Trial Routes - Protected by authentication
Route::prefix('ai-trial')->middleware('auth')->group(function () {
    Route::get('/', [AiTrialController::class, 'index'])->name('ai-trial.index');
    Route::post('/process', [AiTrialController::class, 'process'])->name('ai-trial.process');
});

// Breeze Routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
