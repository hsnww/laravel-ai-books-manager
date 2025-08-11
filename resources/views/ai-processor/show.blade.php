<!DOCTYPE html>
<html lang="{{ session('locale', 'en') }}" dir="{{ session('locale', 'en') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('AI Processor') }} - {{ $bookId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .loading { display: none; }
        .result-item { margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; }
        .success { background-color: #d1fae5; border: 1px solid #10b981; }
        .error { background-color: #fee2e2; border: 1px solid #ef4444; }
        .processing { background-color: #fef3c7; border: 1px solid #f59e0b; }
        
        /* تحسين عرض أسماء الملفات */
        .file-name {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            line-height: 1.4;
        }
        
        .file-card {
            min-height: 80px;
            display: flex;
            flex-direction: column;
        }
        
        /* تحسين مظهر radio buttons */
        .form-radio:checked {
            background-color: #9333ea;
            border-color: #9333ea;
        }
        
        .form-radio:focus {
            ring-color: #9333ea;
        }
        
        /* تحسين مظهر checkboxes */
        .form-checkbox:checked {
            background-color: #9333ea;
            border-color: #9333ea;
        }
        
        .form-checkbox:focus {
            ring-color: #9333ea;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
                 <!-- Header -->
         <div class="mb-8">
             <!-- Back to Admin Panel -->
             <div class="mb-4">
                 <a href="{{ route('filament.admin.pages.dashboard') }}" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                     <i class="fas fa-arrow-right ml-2"></i>
                     {{ __('Back to Admin Panel') }}
                 </a>
             </div>
             
             <h1 class="text-3xl font-bold text-gray-800 mb-2">
                 <i class="fas fa-brain text-blue-600"></i>
                 {{ __('AI Processor') }}
             </h1>
             <p class="text-gray-600">{{ __('Process extracted texts using Google Gemini AI') }}</p>
             
             <!-- Book Information -->
             @if($bookInfo)
                 <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                     <div class="flex items-center space-x-4 rtl:space-x-reverse">
                         <i class="fas fa-book text-blue-600 text-xl"></i>
                         <div>
                             <h3 class="text-lg font-semibold text-blue-800">{{ $bookInfo->title }}</h3>
                             @if($bookInfo->author)
                                 <p class="text-sm text-blue-600">{{ __('Author') }}: {{ $bookInfo->author }}</p>
                             @endif
                             @if($bookInfo->language)
                                 <p class="text-sm text-blue-600">{{ __('Language') }}: {{ $bookInfo->language }}</p>
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

                 <!-- File Selection -->
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
             <h2 class="text-xl font-semibold mb-4">
                 <i class="fas fa-file-alt text-green-600"></i>
                 {{ __('File Selection') }}
             </h2>
             
             <!-- Select All / Deselect All Buttons -->
             <div class="mb-4 flex gap-2">
                 <button type="button" id="selectAllBtn" 
                         class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                     <i class="fas fa-check-square mr-2"></i>
                     {{ __('Select All') }}
                 </button>
                 <button type="button" id="deselectAllBtn" 
                         class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200">
                     <i class="fas fa-square mr-2"></i>
                     {{ __('Deselect All') }}
                 </button>
             </div>
             
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                @foreach($files as $file)
                <div class="border rounded-lg p-3 hover:bg-gray-50 file-card">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="selected_files[]" value="{{ $file['filename'] }}" 
                               class="form-checkbox h-4 w-4 text-blue-600 rounded">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 file-name" title="{{ $file['filename'] }}">
                                @if(strlen($file['filename']) > 30)
                                    {{ substr($file['filename'], 0, 30) }}...
                                @else
                                    {{ $file['filename'] }}
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ number_format($file['file_size']) }} bytes</div>
                        </div>
                    </label>
                </div>
                @endforeach
            </div>
            
            <div class="text-sm text-gray-600">
                <i class="fas fa-info-circle"></i>
                {{ __('Found') }} {{ count($files) }} {{ __('text files') }}
            </div>
        </div>

        <!-- Processing Statistics -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-chart-bar text-green-600"></i>
                {{ __('Previous Processing Statistics') }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Summarized Texts -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-compress-alt text-2xl"></i>
                        <span class="text-sm opacity-90">{{ __('Summary') }}</span>
                    </div>
                    <div class="space-y-1">
                        @if(isset($processingStats['summarized']) && $processingStats['summarized']->count() > 0)
                            @foreach($processingStats['summarized'] as $stat)
                                <div class="text-sm">
                                    {{ $stat->count }} {{ $stat->target_language }}
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm opacity-75">{{ __('No processing found') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Formatting Texts -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-list-ul text-2xl"></i>
                        <span class="text-sm opacity-90">{{ __('Bullet Points') }}</span>
                    </div>
                    <div class="space-y-1">
                        @if(isset($processingStats['formatting']) && $processingStats['formatting']->count() > 0)
                            @foreach($processingStats['formatting'] as $stat)
                                <div class="text-sm">
                                    {{ $stat->count }} {{ $stat->target_language }}
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm opacity-75">{{ __('No processing found') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Translated Texts -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-language text-2xl"></i>
                        <span class="text-sm opacity-90">{{ __('Translation') }}</span>
                    </div>
                    <div class="space-y-1">
                        @if(isset($processingStats['translated']) && $processingStats['translated']->count() > 0)
                            @foreach($processingStats['translated'] as $stat)
                                <div class="text-sm">
                                    {{ $stat->count }} {{ $stat->target_language }}
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm opacity-75">{{ __('No processing found') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Enhanced Texts -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-magic text-2xl"></i>
                        <span class="text-sm opacity-90">{{ __('Enhancement') }}</span>
                    </div>
                    <div class="space-y-1">
                        @if(isset($processingStats['enhanced']) && $processingStats['enhanced']->count() > 0)
                            @foreach($processingStats['enhanced'] as $stat)
                                <div class="text-sm">
                                    {{ $stat->count }} {{ $stat->target_language }}
                                </div>
                            @endforeach
                        @else
                            <div class="text-sm opacity-75">{{ __('No processing found') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Options -->
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
             <h2 class="text-xl font-semibold mb-4">
                 <i class="fas fa-cogs text-purple-600"></i>
                 {{ __('Processing Options') }}
             </h2>
             
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @foreach($processingOptions as $key => $label)
                <div class="border rounded-lg p-3 hover:bg-gray-50 transition duration-200">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="processing_options[]" value="{{ $key }}" 
                               class="form-checkbox h-4 w-4 text-purple-600 focus:ring-purple-500">
                        <div class="font-medium text-gray-900">{{ $label }}</div>
                    </label>
                </div>
                @endforeach
            </div>
            
            <div class="text-sm text-gray-600 mt-2">
                <i class="fas fa-info-circle"></i>
                {{ __('Choose one or more processing types. Book Info and Blog Article will merge all files into one, while Summaries and other types will process each file separately.') }}
            </div>
        </div>

        <!-- Output Method -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-file-export text-indigo-600"></i>
                {{ __('Output Method') }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-3 hover:bg-gray-50 transition duration-200">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="output_method" value="single" 
                               class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500" checked>
                        <div>
                            <div class="font-medium text-gray-900">{{ __('Single File Output') }}</div>
                            <div class="text-sm text-gray-600">{{ __('Merge all selected files into one output file') }}</div>
                        </div>
                    </label>
                </div>
                
                <div class="border rounded-lg p-3 hover:bg-gray-50 transition duration-200">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="output_method" value="multiple" 
                               class="form-radio h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <div class="font-medium text-gray-900">{{ __('Multiple Files Output') }}</div>
                            <div class="text-sm text-gray-600">{{ __('Process each file separately and create individual output files') }}</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="text-sm text-gray-600 mt-2">
                <i class="fas fa-info-circle"></i>
                {{ __('Choose single file for merged processing, multiple files for individual file processing. Multiple processing types always use single file method.') }}
            </div>
        </div>

        <!-- Target Language -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-language text-orange-600"></i>
                {{ __('Select Output Language') }}
            </h2>
            
            <select name="target_language" id="target_language" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">{{ __('Select Output Language') }}</option>
                @foreach($availableLanguages as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Process Button -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <button id="processBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                <i class="fas fa-play mr-2"></i>
                {{ __('Start Processing') }}
            </button>
            
            <div id="loading" class="loading mt-4 text-center">
                <div class="inline-flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-gray-600">{{ __('Processing...') }}</span>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div id="results" class="bg-white rounded-lg shadow-md p-6" style="display: none;">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-chart-bar text-green-600"></i>
                {{ __('Processing Results') }}
            </h2>
            
            <div id="resultsContent"></div>
        </div>

        <!-- Processing History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-history text-gray-600"></i>
                تاريخ المعالجات
                <span id="historyCount" class="text-sm text-gray-500 ml-2"></span>
            </h2>
            
            <div id="historyContent" class="space-y-2">
                <div class="text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin"></i>
                    جاري تحميل التاريخ...
                </div>
            </div>
            
            <div id="loadMoreContainer" class="mt-4 text-center" style="display: none;">
                <button id="loadMoreBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    عرض المزيد
                </button>
            </div>
        </div>
    </div>

         <script>
         // Select All / Deselect All functionality for files
         document.getElementById('selectAllBtn').addEventListener('click', function() {
             document.querySelectorAll('input[name="selected_files[]"]').forEach(checkbox => {
                 checkbox.checked = true;
             });
         });
         
         document.getElementById('deselectAllBtn').addEventListener('click', function() {
             document.querySelectorAll('input[name="selected_files[]"]').forEach(checkbox => {
                 checkbox.checked = false;
             });
         });
         

         
         // Process files
         document.getElementById('processBtn').addEventListener('click', function() {
            const selectedFiles = Array.from(document.querySelectorAll('input[name="selected_files[]"]:checked'))
                .map(cb => cb.value);
            
            const processingOptions = Array.from(document.querySelectorAll('input[name="processing_options[]"]:checked'))
                .map(cb => cb.value);
            
            const outputMethod = document.querySelector('input[name="output_method"]:checked').value;
            
            const targetLanguage = document.getElementById('target_language').value;
            
            if (selectedFiles.length === 0) {
                alert('يرجى اختيار ملف واحد على الأقل');
                return;
            }
            
            if (processingOptions.length === 0) {
                alert('يرجى اختيار نوع معالجة واحد على الأقل');
                return;
            }
            
            // إظهار رسالة تأكيد عند اختيار أنواع متعددة
            if (processingOptions.length > 1) {
                const confirmMessage = `تم اختيار ${processingOptions.length} أنواع معالجة. سيتم معالجتها بالتتالي:\n\n` +
                    processingOptions.map(option => `• ${option}`).join('\n') + 
                    '\n\nملاحظة مهمة:\n• استخراج معلومات الكتاب ومقال المدونة: سيتم دمج جميع الملفات في ملف واحد\n• الملخص والترجمة والتحسين: كل ملف سيتم معالجته منفصلاً\n\nهل تريد المتابعة؟';
                
                if (!confirm(confirmMessage)) {
                    return;
                }
            }

            if (!outputMethod) {
                alert('يرجى اختيار طريقة الإخراج');
                return;
            }
            
            if (!targetLanguage) {
                alert('يرجى اختيار لغة المخرجات');
                return;
            }
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('processBtn').disabled = true;
            
            // Send request
            const sendBatch = (offset = 0) => {
                fetch('{{ route("ai-processor.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        book_id: '{{ $bookId }}',
                        selected_files: selectedFiles,
                        processing_options: processingOptions,
                        output_method: outputMethod,
                        target_language: targetLanguage,
                        offset: offset,
                        max_duration_seconds: 60
                    })
                })
                .then(async response => {
                    const raw = await response.text();
                    let data;
                    try {
                        data = raw ? JSON.parse(raw) : {};
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + raw.slice(0, 200));
                    }

                    if (!response.ok || data.success === false) {
                        const message = (data && (data.error || data.message)) || ('HTTP ' + response.status);
                        throw new Error(message);
                    }

                    return data;
                })
                .then(data => {
                    // Append results
                    if (Array.isArray(data.results) && data.results.length) {
                        const existing = document.getElementById('resultsContent').innerHTML;
                        // Temporarily render new results using the same presenter
                        showResults(data.results);
                        document.getElementById('results').style.display = 'block';
                    }

                    if (data.has_more) {
                        // Continue next batch
                        sendBatch(data.next_offset);
                    } else {
                        // Done
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('processBtn').disabled = false;
                        loadHistory();
                    }
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('processBtn').disabled = false;
                    console.error('Process request failed:', error);
                    alert('تعذر قراءة استجابة الخادم. قد تكون المعالجة اكتملت جزئياً. راجع السجل وجرّب مرة أخرى.');
                });
            };

            // Start first batch
            sendBatch(0);
        });
        
        // Show results
        function showResults(results) {
            const resultsDiv = document.getElementById('results');
            const contentDiv = document.getElementById('resultsContent');
            
            let html = '';
            
            results.forEach(result => {
                const statusClass = result.success ? 'success' : 'error';
                const statusIcon = result.success ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600';
                
                html += `
                    <div class="result-item ${statusClass}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <i class="${statusIcon} mr-2"></i>
                                <span class="font-medium">${result.filename}</span>
                            </div>
                            ${result.processing_time ? `<span class="text-sm text-gray-600">${result.processing_time}ms</span>` : ''}
                        </div>
                        
                        ${result.success ? 
                            `<div class="text-sm text-gray-700">تمت المعالجة بنجاح</div>` :
                            `<div class="text-sm text-red-700">${result.error}</div>`
                        }
                    </div>
                `;
            });
            
            contentDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }
        
        // Global variables for history pagination
        let historyOffset = 0;
        let historyLimit = 10;
        let allHistory = [];
        
        // Load processing history
        function loadHistory(reset = true) {
            if (reset) {
                historyOffset = 0;
                allHistory = [];
            }
            
            const url = '{{ route("ai-processor.history", $bookId) }}?limit=' + historyLimit + '&offset=' + historyOffset;
            
            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (reset) {
                        allHistory = data.history;
                    } else {
                        allHistory = allHistory.concat(data.history);
                    }
                    
                    showHistory(allHistory, data.total_count, data.has_more);
                    historyOffset += historyLimit;
                }
            })
            .catch(error => {
                console.error('Error loading history:', error);
            });
        }
        
        // Show history
        function showHistory(history, totalCount, hasMore) {
            const contentDiv = document.getElementById('historyContent');
            const countSpan = document.getElementById('historyCount');
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            
            // Update count display
            if (totalCount > 0) {
                countSpan.textContent = `(${history.length} من ${totalCount})`;
            } else {
                countSpan.textContent = '';
            }
            
            if (history.length === 0) {
                                    contentDiv.innerHTML = '<div class="text-center text-gray-500">{{ __("No previous processing") }}</div>';
                loadMoreContainer.style.display = 'none';
                return;
            }
            
            let html = '';
            
            history.forEach(item => {
                const statusClass = item.processing_status === 'success' ? 'text-green-600' : 
                                  item.processing_status === 'failed' ? 'text-red-600' : 'text-yellow-600';
                const statusIcon = item.processing_status === 'success' ? 'fas fa-check-circle' : 
                                 item.processing_status === 'failed' ? 'fas fa-times-circle' : 'fas fa-clock';
                
                html += `
                    <div class="border rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <i class="${statusIcon} ${statusClass} mr-2"></i>
                                <span class="font-medium">${item.original_file}</span>
                            </div>
                            <span class="text-sm text-gray-500">${new Date(item.created_at).toLocaleString('ar-SA')}</span>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <div>النوع: ${item.processing_type}</div>
                            <div>اللغة: ${item.target_language}</div>
                            ${item.processing_time_seconds ? `<div>الوقت: ${item.processing_time_seconds}s</div>` : ''}
                            ${item.error_message ? `<div class="text-red-600">الخطأ: ${item.error_message}</div>` : ''}
                        </div>
                    </div>
                `;
            });
            
            contentDiv.innerHTML = html;
            
            // Show/hide load more button
            if (hasMore) {
                loadMoreContainer.style.display = 'block';
            } else {
                loadMoreContainer.style.display = 'none';
            }
        }
        
        // Load more history
        document.getElementById('loadMoreBtn').addEventListener('click', function() {
            loadHistory(false); // Don't reset, append to existing
        });
        
        // Load history on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadHistory();
        });
    </script>
</body>
</html> 