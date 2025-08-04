<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FileManager;

class FindPdfFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:pdf-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find PDF files in uploads folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Searching for PDF files in uploads folder...");
        
        $pdfFiles = FileManager::where('folder', 'uploads')
            ->where('type', 'like', '%pdf%')
            ->get(['id', 'name', 'path', 'type']);
        
        if ($pdfFiles->isEmpty()) {
            $this->warn("No PDF files found in uploads folder!");
            
            // عرض جميع الملفات
            $this->info("\nAll files:");
            FileManager::all(['id', 'name', 'folder', 'type'])->each(function($file) {
                $this->line("ID: {$file->id}, Name: {$file->name}, Folder: {$file->folder}, Type: {$file->type}");
            });
            
            return 1;
        }
        
        $this->info("Found " . $pdfFiles->count() . " PDF file(s):");
        
        $pdfFiles->each(function($file) {
            $this->line("ID: {$file->id}, Name: {$file->name}, Path: {$file->path}");
        });
        
        return 0;
    }
}
