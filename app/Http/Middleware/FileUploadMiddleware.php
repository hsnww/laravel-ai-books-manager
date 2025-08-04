<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileUploadMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // زيادة حدود رفع الملفات
        if (function_exists('ini_set')) {
            ini_set('upload_max_filesize', '50M');
            ini_set('post_max_size', '50M');
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');
            ini_set('max_input_time', 300);
            ini_set('max_input_vars', 10000);
        }
        
        // إضافة headers مخصصة
        if ($request->is('admin/*')) {
            $response = $next($request);
            $response->headers->set('X-File-Upload-Limit', '50MB');
            return $response;
        }
        
        return $next($request);
    }
}
