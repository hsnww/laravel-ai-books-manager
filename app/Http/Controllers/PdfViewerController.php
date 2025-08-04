<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\FileManager;

class PdfViewerController extends Controller
{
    /**
     * عرض ملف PDF
     */
    public function show($id)
    {
        $file = FileManager::findOrFail($id);
        
        // التحقق من أن الملف PDF
        if ($file->type !== 'pdf') {
            abort(404, 'الملف ليس PDF');
        }
        
        // التحقق من وجود الملف
        $filePath = storage_path('app/public/' . $file->path);
        if (!file_exists($filePath)) {
            // محاولة البحث في مجلد uploads
            $uploadsPath = storage_path('app/public/uploads/' . $file->name);
            if (file_exists($uploadsPath)) {
                $file->path = 'uploads/' . $file->name;
            } else {
                abort(404, 'الملف غير موجود');
            }
        }
        
        return view('pdf-viewer.show', compact('file'));
    }
    
    /**
     * تحميل ملف PDF
     */
    public function download($id)
    {
        $file = FileManager::findOrFail($id);
        
        if ($file->type !== 'pdf') {
            abort(404, 'الملف ليس PDF');
        }
        
        $filePath = storage_path('app/public/' . $file->path);
        if (!file_exists($filePath)) {
            // محاولة البحث في مجلد uploads
            $uploadsPath = storage_path('app/public/uploads/' . $file->name);
            if (file_exists($uploadsPath)) {
                $filePath = $uploadsPath;
            } else {
                abort(404, 'الملف غير موجود');
            }
        }
        
        return response()->download($filePath, $file->name);
    }
}
