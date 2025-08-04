<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DownloadLanguageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesseract:download-languages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحميل ملفات اللغة الأوروبية لـ Tesseract OCR';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 تحميل ملفات اللغة الأوروبية لـ Tesseract OCR');
        $this->line('=' . str_repeat('=', 50));

        // التحقق من وجود Python
        $pythonPath = $this->findPython();
        if (!$pythonPath) {
            $this->error('❌ لم يتم العثور على Python');
            return 1;
        }

        $this->info("✅ تم العثور على Python: $pythonPath");

        // التحقق من وجود سكربت التحميل
        $scriptPath = base_path('scripts/download_language_files.py');
        if (!file_exists($scriptPath)) {
            $this->error('❌ لم يتم العثور على سكربت التحميل');
            return 1;
        }

        $this->info("✅ تم العثور على سكربت التحميل");

        // تشغيل سكربت Python
        $this->info('🚀 تشغيل سكربت التحميل...');
        $this->line('');

        try {
            $result = Process::run("$pythonPath \"$scriptPath\"");
            
            if ($result->successful()) {
                $this->info('✅ تم تشغيل سكربت التحميل بنجاح');
                $this->line('');
                $this->info('📋 ملخص التحميل:');
                $this->line($result->output());
            } else {
                $this->error('❌ فشل في تشغيل سكربت التحميل');
                $this->line($result->errorOutput());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ خطأ في تشغيل سكربت التحميل: ' . $e->getMessage());
            return 1;
        }

        $this->line('');
        $this->info('🔍 للتحقق من التثبيت، قم بتشغيل:');
        $this->line('tesseract --list-langs');

        return 0;
    }

    /**
     * البحث عن Python في النظام
     */
    private function findPython(): ?string
    {
        $pythonCommands = ['python', 'python3', 'py'];
        
        foreach ($pythonCommands as $command) {
            try {
                $result = Process::run("$command --version");
                if ($result->successful()) {
                    return $command;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return null;
    }
} 