<x-filament-panels::page>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    ملفات المجلد: {{ $this->folderName }}
                </h2>
                <p class="text-gray-600">
                    عرض جميع الملفات في مجلد {{ $this->folderName }}
                </p>
            </div>
            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                <a href="{{ route('filament.admin.resources.file-managers.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 ml-2 rtl:ml-0 rtl:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    العودة للمجلدات
                </a>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page> 