<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إدارة الملفات النصية - {{ $bookId }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .file-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .file-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        
        .file-content {
            padding: 20px;
        }
        
        .word-count {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .file-size {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .btn-action {
            margin: 5px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .modal-content {
            border-radius: 15px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .text-editor {
            min-height: 400px;
            font-family: 'Courier New', monospace;
            direction: rtl;
            text-align: right;
        }
        
        .drag-handle {
            cursor: move;
            color: #6c757d;
        }
        
        .reorder-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: move;
        }
        
        .reorder-item:hover {
            border-color: #667eea;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <!-- Back to Admin Panel -->
            <div class="mb-4">
                <a href="{{ route('filament.admin.resources.file-managers.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-arrow-right ml-2"></i>
                    العودة للوحة التحكم
                </a>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-folder-open text-blue-600"></i>
                إدارة الملفات النصية
            </h1>
            <p class="text-gray-600">إدارة وتنظيم الملفات النصية المستخرجة من الكتاب</p>
            
            <!-- Book Information -->
            @if($bookInfo)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center space-x-4 rtl:space-x-reverse">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-800">{{ $bookInfo->title }}</h3>
                            @if($bookInfo->author)
                                <p class="text-sm text-blue-600">المؤلف: {{ $bookInfo->author }}</p>
                            @endif
                            @if($bookInfo->language)
                                <p class="text-sm text-blue-600">اللغة: {{ $bookInfo->language }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="mt-4">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                    <i class="fas fa-folder"></i>
                    {{ $bookId }}
                </span>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-chart-bar text-green-600"></i>
                إحصائيات الملفات
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-file-alt text-2xl"></i>
                        <span class="text-sm opacity-90">إجمالي الملفات</span>
                    </div>
                    <div class="text-2xl font-bold" id="totalFiles">0</div>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-font text-2xl"></i>
                        <span class="text-sm opacity-90">إجمالي الكلمات</span>
                    </div>
                    <div class="text-2xl font-bold" id="totalWords">0</div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-weight text-2xl"></i>
                        <span class="text-sm opacity-90">إجمالي الحجم</span>
                    </div>
                    <div class="text-2xl font-bold" id="totalSize">0 KB</div>
                </div>
                
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                        <span class="text-sm opacity-90">الملفات الفارغة</span>
                    </div>
                    <div class="text-2xl font-bold" id="emptyFiles">0</div>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-tools text-purple-600"></i>
                أدوات الإدارة
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center" onclick="showMergeModal()">
                    <i class="fas fa-object-group mr-2"></i>
                    دمج الملفات
                </button>
                
                <button class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center" onclick="showReorderModal()">
                    <i class="fas fa-sort mr-2"></i>
                    إعادة الترتيب
                </button>
                
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center" onclick="showDeleteModal()">
                    <i class="fas fa-trash mr-2"></i>
                    حذف متعدد
                </button>
                
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center" onclick="downloadAll()">
                    <i class="fas fa-download mr-2"></i>
                    تحميل الكل
                </button>
            </div>
        </div>

        <!-- Files List -->
        <div id="filesContainer" class="space-y-4">
            <!-- Files will be loaded here -->
        </div>
    </div>

    <!-- Edit File Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        تعديل الملف: <span id="editFileName" class="font-semibold"></span>
                    </h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="flex justify-between items-center mb-4">
                    <div class="bg-blue-50 text-blue-700 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-font mr-1"></i>
                        عدد الكلمات: <span id="wordCount" class="font-semibold">0</span>
                    </div>
                    <button onclick="copyToClipboard()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-copy mr-1"></i>
                        نسخ النص
                    </button>
                </div>
                
                <textarea id="editContent" class="w-full h-96 p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm" style="direction: rtl; text-align: right;"></textarea>
                
                <div class="flex justify-end space-x-3 mt-4">
                    <button onclick="closeEditModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button onclick="saveFile()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Split File Modal -->
    <div id="splitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-scissors text-yellow-600 mr-2"></i>
                        تقسيم الملف
                    </h3>
                    <button onclick="closeSplitModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">علامة التقسيم:</label>
                    <input type="text" id="splitMarker" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="#####" placeholder="علامة التقسيم">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">معاينة التقسيم:</label>
                    <div id="splitPreview" class="border border-gray-300 rounded-lg p-3 max-h-60 overflow-y-auto bg-gray-50">
                        <!-- Preview will be shown here -->
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeSplitModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button onclick="previewSplit()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-eye mr-1"></i>
                        معاينة
                    </button>
                    <button onclick="splitFileExecute()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-scissors mr-1"></i>
                        تقسيم
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Files Modal -->
    <div id="mergeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-object-group text-blue-600 mr-2"></i>
                        دمج الملفات
                    </h3>
                    <button onclick="closeMergeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختر الملفات المراد دمجها:</label>
                    <select id="filesToMerge" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" multiple size="8">
                        <!-- Files will be loaded here -->
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">عنوان الملف المدموج:</label>
                    <input type="text" id="mergedTitle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="أدخل عنوان الملف المدموج">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeMergeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button onclick="mergeFiles()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-object-group mr-1"></i>
                        دمج
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder Modal -->
    <div id="reorderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-sort text-yellow-600 mr-2"></i>
                        إعادة ترتيب الفصول
                    </h3>
                    <button onclick="closeReorderModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="reorderContainer" class="space-y-3 max-h-96 overflow-y-auto">
                    <!-- Reorder items will be loaded here -->
                </div>
                
                <div class="flex justify-end space-x-3 mt-4">
                    <button onclick="closeReorderModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button onclick="reorderChapters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-sort mr-1"></i>
                        إعادة الترتيب
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        حذف متعدد
                    </h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختر الملفات المراد حذفها:</label>
                    <div id="deleteFilesContainer" class="space-y-2 max-h-60 overflow-y-auto">
                        <!-- Files will be loaded here -->
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        <span class="text-sm text-yellow-800 font-medium">تحذير:</span>
                    </div>
                    <p class="text-sm text-yellow-700 mt-1">سيتم إنشاء نسخة احتياطية قبل الحذف.</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        إلغاء
                    </button>
                    <button onclick="deleteMultipleFiles()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-trash mr-1"></i>
                        حذف المحدد
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        let files = [];
        let currentFile = null;
        
        // Load files on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFiles();
        });
        
        // Modal functions
        function showModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
        
        function closeEditModal() {
            hideModal('editModal');
        }
        
        function closeSplitModal() {
            hideModal('splitModal');
        }
        
        function closeMergeModal() {
            hideModal('mergeModal');
        }
        
        function closeReorderModal() {
            hideModal('reorderModal');
        }
        
        function closeDeleteModal() {
            hideModal('deleteModal');
        }
        
        // Load files from server
        function loadFiles() {
            fetch(`/file-manager/{{ $bookId }}/files`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        files = data.files;
                        renderFiles();
                        updateStats();
                    }
                })
                .catch(error => {
                    console.error('Error loading files:', error);
                    showAlert('خطأ في تحميل الملفات', 'danger');
                });
        }
        
        // Render files in the container
        function renderFiles() {
            const container = document.getElementById('filesContainer');
            container.innerHTML = '';
            
            // Create a grid wrapper
            const gridWrapper = document.createElement('div');
            gridWrapper.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
            
            files.forEach(file => {
                const fileCard = createFileCard(file);
                gridWrapper.appendChild(fileCard);
            });
            
            container.appendChild(gridWrapper);
        }
        
        // Create file card
        function createFileCard(file) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200';
            
            // Truncate filename to 20 characters
            const displayName = file.filename.length > 20 ? file.filename.substring(0, 20) + '...' : file.filename;
            
            card.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h6 class="text-lg font-semibold text-gray-800" title="${file.filename}">
                        <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                        ${displayName}
                    </h6>
                    <div class="flex items-center">
                        <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" 
                               type="checkbox" value="${file.filename}" id="check_${file.filename}">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-blue-50 text-blue-700 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-font mr-1"></i>
                        ${file.word_count} كلمة
                    </div>
                    <div class="bg-purple-50 text-purple-700 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-weight-hanging mr-1"></i>
                        ${formatFileSize(file.file_size)}
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition duration-200 flex items-center justify-center" onclick="editFile('${file.filename}')">
                        <i class="fas fa-edit mr-1"></i>
                        تعديل
                    </button>
                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition duration-200 flex items-center justify-center" onclick="splitFile('${file.filename}')">
                        <i class="fas fa-scissors mr-1"></i>
                        تقسيم
                    </button>
                    <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition duration-200 flex items-center justify-center" onclick="deleteFile('${file.filename}')">
                        <i class="fas fa-trash mr-1"></i>
                        حذف
                    </button>
                </div>
            `;
            return card;
        }
        
        // Update statistics
        function updateStats() {
            const totalFiles = files.length;
            const totalWords = files.reduce((sum, file) => sum + file.word_count, 0);
            const totalSize = files.reduce((sum, file) => sum + file.file_size, 0);
            const emptyFiles = files.filter(file => file.word_count === 0).length;
            
            document.getElementById('totalFiles').textContent = totalFiles;
            document.getElementById('totalWords').textContent = totalWords.toLocaleString();
            document.getElementById('totalSize').textContent = Math.round(totalSize / 1024);
            document.getElementById('emptyFiles').textContent = emptyFiles;
        }
        
        // Edit file
        function editFile(filename) {
            const file = files.find(f => f.filename === filename);
            if (file) {
                currentFile = file;
                document.getElementById('editFileName').textContent = filename;
                document.getElementById('editContent').value = file.content;
                document.getElementById('wordCount').textContent = file.word_count;
                
                showModal('editModal');
            }
        }
        
        // Save file
        function saveFile() {
            if (!currentFile) return;
            
            const content = document.getElementById('editContent').value;
            
            fetch('/file-manager/edit-file', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    book_id: '{{ $bookId }}',
                    filename: currentFile.filename,
                    content: content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                    hideModal('editModal');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error saving file:', error);
                showAlert('خطأ في حفظ الملف', 'danger');
            });
        }
        
        // Split file
        function splitFile(filename) {
            console.log('splitFile called with filename:', filename);
            currentFile = files.find(f => f.filename === filename);
            console.log('currentFile found:', currentFile);
            if (currentFile) {
                showModal('splitModal');
            } else {
                console.error('File not found:', filename);
                showAlert('الملف غير موجود', 'danger');
            }
        }
        
        // Preview split
        function previewSplit() {
            if (!currentFile) return;
            
            const marker = document.getElementById('splitMarker').value;
            const content = currentFile.content;
            const sections = content.split(marker);
            const validSections = sections.filter(section => section.trim().length > 0);
            
            const preview = document.getElementById('splitPreview');
            preview.innerHTML = `
                <h6 class="font-semibold mb-2">سيتم تقسيم الملف إلى ${validSections.length} فصل:</h6>
                <ul class="space-y-1">
                    ${validSections.map((section, index) => {
                        const lines = section.split('\n');
                        const title = lines[0].trim();
                        return `<li class="text-sm"><strong>${index + 1}.</strong> ${title} (${section.split(' ').length} كلمة)</li>`;
                    }).join('')}
                </ul>
            `;
        }
        
        // Execute split
        function splitFileExecute() {
            if (!currentFile) {
                console.error('No current file selected');
                showAlert('لم يتم اختيار ملف', 'danger');
                return;
            }
            
            const marker = document.getElementById('splitMarker').value;
            const requestData = {
                book_id: '{{ $bookId }}',
                filename: currentFile.filename,
                marker: marker
            };
            
            console.log('Sending split request:', requestData);
            
            fetch('/file-manager/split-file', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                    hideModal('splitModal');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error splitting file:', error);
                showAlert('خطأ في تقسيم الملف', 'danger');
            });
        }
        
        // Show merge modal
        function showMergeModal() {
            const select = document.getElementById('filesToMerge');
            select.innerHTML = '';
            
            files.forEach(file => {
                const option = document.createElement('option');
                option.value = file.filename;
                option.textContent = file.filename;
                select.appendChild(option);
            });
            
            showModal('mergeModal');
        }
        
        // Merge files
        function mergeFiles() {
            const selectedFiles = Array.from(document.getElementById('filesToMerge').selectedOptions).map(option => option.value);
            const newTitle = document.getElementById('mergedTitle').value;
            
            if (selectedFiles.length < 2) {
                showAlert('يجب اختيار ملفين على الأقل للدمج', 'warning');
                return;
            }
            
            if (!newTitle) {
                showAlert('يجب إدخال عنوان للملف المدموج', 'warning');
                return;
            }
            
            fetch('/file-manager/merge-files', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    book_id: '{{ $bookId }}',
                    files_to_merge: selectedFiles,
                    new_title: newTitle
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                    hideModal('mergeModal');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error merging files:', error);
                showAlert('خطأ في دمج الملفات', 'danger');
            });
        }
        
        // Show reorder modal
        function showReorderModal() {
            const container = document.getElementById('reorderContainer');
            container.innerHTML = '';
            
            files.forEach((file, index) => {
                const order = extractOrder(file.filename);
                const displayName = file.filename.length > 40 ? file.filename.substring(0, 40) + '...' : file.filename;
                const item = document.createElement('div');
                item.className = 'bg-white border border-gray-300 rounded-lg p-4 flex justify-between items-center';
                item.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-grip-vertical text-gray-400 mr-3 cursor-move"></i>
                        <strong class="text-gray-800" title="${file.filename}">${displayName}</strong>
                    </div>
                    <div>
                        <input type="number" class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               value="${order}" min="1" max="9999" 
                               onchange="updateOrder('${file.filename}', this.value)">
                    </div>
                `;
                container.appendChild(item);
            });
            
            // Initialize sortable
            new Sortable(container, {
                animation: 150,
                ghostClass: 'opacity-50'
            });
            
            showModal('reorderModal');
        }
        
        // Extract order from filename
        function extractOrder(filename) {
            const match = filename.match(/^(\d+)_/);
            return match ? parseInt(match[1]) : 9999;
        }
        
        // Update order
        function updateOrder(filename, newOrder) {
            // This will be handled in the reorder function
        }
        
        // Reorder chapters
        function reorderChapters() {
            const items = document.querySelectorAll('#reorderContainer > div');
            const reorderData = [];
            
            items.forEach((item, index) => {
                const filename = item.querySelector('strong').textContent;
                const newOrder = parseInt(item.querySelector('input').value);
                
                reorderData.push({
                    filename: filename,
                    new_order: newOrder
                });
            });
            
            fetch('/file-manager/reorder-chapters', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    book_id: '{{ $bookId }}',
                    reorder_data: reorderData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                    hideModal('reorderModal');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error reordering chapters:', error);
                showAlert('خطأ في إعادة الترتيب', 'danger');
            });
        }
        
        // Show delete modal
        function showDeleteModal() {
            const container = document.getElementById('deleteFilesContainer');
            container.innerHTML = '';
            
            files.forEach(file => {
                const displayName = file.filename.length > 35 ? file.filename.substring(0, 35) + '...' : file.filename;
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-3 p-2 bg-gray-50 rounded-lg';
                div.innerHTML = `
                    <input class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500" 
                           type="checkbox" value="${file.filename}" id="delete_${file.filename}">
                    <label class="text-sm text-gray-700 cursor-pointer" for="delete_${file.filename}" title="${file.filename}">
                        ${displayName} (${file.word_count} كلمة)
                    </label>
                `;
                container.appendChild(div);
            });
            
            showModal('deleteModal');
        }
        
        // Delete multiple files
        function deleteMultipleFiles() {
            const selectedFiles = Array.from(document.querySelectorAll('#deleteFilesContainer input:checked')).map(input => input.value);
            
            if (selectedFiles.length === 0) {
                showAlert('يجب اختيار ملف واحد على الأقل للحذف', 'warning');
                return;
            }
            
            if (!confirm(`هل أنت متأكد من حذف ${selectedFiles.length} ملف؟`)) {
                return;
            }
            
            fetch('/file-manager/delete-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    book_id: '{{ $bookId }}',
                    filenames: selectedFiles
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                    hideModal('deleteModal');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error deleting files:', error);
                showAlert('خطأ في حذف الملفات', 'danger');
            });
        }
        
        // Delete single file
        function deleteFile(filename) {
            if (!confirm(`هل أنت متأكد من حذف الملف ${filename}؟`)) {
                return;
            }
            
            fetch('/file-manager/delete-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    book_id: '{{ $bookId }}',
                    filenames: [filename]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadFiles(); // Reload files
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error deleting file:', error);
                showAlert('خطأ في حذف الملف', 'danger');
            });
        }
        
        // Download all files
        function downloadAll() {
            // Create a zip file with all text files
            // This would require additional backend implementation
            showAlert('سيتم إضافة هذه الميزة قريباً', 'info');
        }
        
        // Copy to clipboard
        function copyToClipboard() {
            const textarea = document.getElementById('editContent');
            textarea.select();
            document.execCommand('copy');
            showAlert('تم نسخ النص إلى الحافظة', 'success');
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Show alert
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'danger' ? 'bg-red-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
            alertDiv.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg fixed top-5 right-5 z-50 min-w-80`;
            alertDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-4">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html> 