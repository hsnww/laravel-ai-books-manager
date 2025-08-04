<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض PDF - {{ $file->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            direction: rtl;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .file-details {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .pdf-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            min-height: 600px;
        }
        
        .pdf-viewer {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 600px;
            font-size: 1.2rem;
            color: #7f8c8d;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .actions {
                justify-content: center;
            }
            
            .pdf-viewer {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="file-info">
                <div class="file-name">{{ $file->name }}</div>
                <div class="file-details">
                    الحجم: {{ number_format($file->size) }} bytes | 
                    النوع: {{ $file->type }} | 
                    المجلد: {{ $file->folder }}
                </div>
            </div>
            <div class="actions">
                <a href="{{ route('pdf-viewer.download', $file->id) }}" class="btn btn-primary">
                    📥 تحميل الملف
                </a>
                <a href="{{ route('filament.admin.resources.file-managers.index') }}" class="btn btn-secondary">
                    ← العودة للقائمة
                </a>
            </div>
        </div>
        
        <div class="pdf-container">
            @php
                $filePath = storage_path('app/public/' . $file->path);
                $uploadsPath = storage_path('app/public/uploads/' . $file->name);
                $fileExists = file_exists($filePath) || file_exists($uploadsPath);
                $displayPath = file_exists($filePath) ? $file->path : 'uploads/' . $file->name;
            @endphp
            
            @if($fileExists)
                <iframe 
                    src="{{ Storage::url($displayPath) }}#toolbar=1&navpanes=1&scrollbar=1" 
                    class="pdf-viewer"
                    title="عرض PDF - {{ $file->name }}"
                >
                    <div class="loading">
                        <div class="spinner"></div>
                        جاري تحميل الملف...
                    </div>
                </iframe>
            @else
                <div class="error-message">
                    ❌ عذراً، الملف غير موجود على الخادم
                </div>
            @endif
        </div>
    </div>
</body>
</html> 