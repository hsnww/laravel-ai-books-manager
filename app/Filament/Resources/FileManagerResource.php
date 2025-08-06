<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileManagerResource\Pages;
use App\Models\FileManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use App\Services\FileManagerService;
use App\Services\BookProcessor;
use App\Services\PdfTextExtractor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TextProcessor;
use App\Services\AiPromptService;

class FileManagerResource extends Resource
{
    protected static ?string $model = FileManager::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function getNavigationGroup(): string
    {
        return __('File Management');
    }
    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('File Management');
    }

    public static function getModelLabel(): string
    {
        return __('Folder');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Folders');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Folder Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->label(__('Path'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('size')
                    ->label(__('Size'))
                    ->numeric()
                    ->suffix('bytes'),
                Forms\Components\TextInput::make('type')
                    ->label(__('Type'))
                    ->maxLength(100),
                Forms\Components\TextInput::make('url')
                    ->label(__('URL'))
                    ->required()
                    ->helperText('أدخل رابط صحيح للملف')
                    ->maxLength(255)
                    ->rules([
                        'required',
                        'url',
                        'max:255'
                    ]),
                Forms\Components\TextInput::make('folder')
                    ->label(__('Folder'))
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('modified_at')
                    ->label(__('Last Modified')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folder_name')
                    ->label(__('Folder Name'))
                    ->getStateUsing(function (FileManager $record) {
                        return self::extractBookId($record->path) ?: $record->name;
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('folder_type')
                    ->label(__('Folder Type'))
                    ->getStateUsing(function (FileManager $record) {
                        return match ($record->folder) {
                            'extracted_texts' => __('Extracted Texts'),
                            'processed_texts' => __('Processed Texts'),
                            default => $record->folder,
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        __('Extracted Texts') => 'success',
                        __('Processed Texts') => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_count')
                    ->label(__('File Count'))
                    ->getStateUsing(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            return FileManager::where('folder', $record->folder)
                                ->where('path', 'like', "{$bookId}%")
                                ->where('path', 'not like', '%backup_%')
                                ->count();
                        }
                        return 0;
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('total_size')
                    ->label(__('Total Size'))
                    ->getStateUsing(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            $totalSize = FileManager::where('folder', $record->folder)
                                ->where('path', 'like', "{$bookId}%")
                                ->where('path', 'not like', '%backup_%')
                                ->sum('size');
                            return number_format($totalSize) . ' bytes';
                        }
                        return '0 bytes';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_modified')
                    ->label(__('Last Modified'))
                    ->getStateUsing(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            $latestFile = FileManager::where('folder', $record->folder)
                                ->where('path', 'like', "{$bookId}%")
                                ->where('path', 'not like', '%backup_%')
                                ->orderBy('modified_at', 'desc')
                                ->first();
                            return $latestFile ? $latestFile->modified_at->format('Y-m-d H:i:s') : '-';
                        }
                        return '-';
                    })
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('folder')
                    ->label(__('Folder Type'))
                    ->options([
                        'extracted_texts' => __('Extracted Texts'),
                        'processed_texts' => __('Processed Texts'),
                    ])
                    ->default('extracted_texts'),
            ])
            ->modifyQueryUsing(function ($query) {
                // استبعاد مجلدات backup من العرض
                $query->where('path', 'not like', '%backup_%');
                
                // استبعاد مجلد uploads (له مورد منفصل)
                $query->where('folder', '!=', 'uploads');
                
                // استخدام GROUP BY للحصول على سجل واحد لكل مجلد كتاب
                $groupedIds = \DB::table('file_managers')
                    ->selectRaw('MIN(id) as id')
                    ->whereIn('folder', ['extracted_texts', 'processed_texts'])
                    ->where('path', 'not like', '%backup_%')
                    ->groupBy(\DB::raw('
                        CASE 
                            WHEN folder = "extracted_texts" THEN SUBSTRING_INDEX(path, "/", 2)
                            WHEN folder = "processed_texts" THEN SUBSTRING_INDEX(path, "/", 2)
                            ELSE path
                        END
                    '))
                    ->pluck('id');
                
                // إضافة المجلدات المجمعة فقط
                return $query->whereIn('id', $groupedIds)->orderBy('modified_at', 'desc');
            })
            ->actions([
                // إجراء عرض ملفات المجلد
                Action::make('view_folder')
                    ->label(__('View Files'))
                    ->icon('heroicon-o-folder-open')
                    ->color('primary')
                    ->url(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            return route('filament.admin.resources.file-managers.folder', ['folder' => $bookId]);
                        }
                        return '#';
                    }),
                
                // إجراء إدارة الملفات (فقط للنصوص المستخرجة)
                Action::make('manage_files')
                    ->label(__('Manage Files'))
                    ->icon('heroicon-o-folder')
                    ->color('success')
                    ->visible(fn (FileManager $record) => $record->folder === 'extracted_texts')
                    ->url(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            return route('file-manager.show', ['bookId' => $bookId]);
                        }
                        return '#';
                    })
                    ->openUrlInNewTab(),
                
                // إجراء معالجة بالذكاء الاصطناعي (فقط للنصوص المستخرجة)
                Action::make('ai_processor')
                    ->label(__('AI Processing'))
                    ->icon('heroicon-o-cpu-chip')
                    ->color('warning')
                    ->visible(fn (FileManager $record) => $record->folder === 'extracted_texts')
                    ->url(function (FileManager $record) {
                        $bookId = self::extractBookId($record->path);
                        if ($bookId) {
                            return route('ai-processor.show', ['bookId' => $bookId]);
                        }
                        return '#';
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('Delete from database')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileManagers::route('/'),
            'folder' => Pages\ViewFolder::route('/{folder}'),
        ];
    }

    /**
     * استخراج معرف الكتاب من المسار أو اسم الملف
     */
    public static function extractBookId($path): string
    {
        // تنظيف المسار من backslashes و forward slashes
        $path = str_replace(['\\', '/'], '/', $path);
        
        // البحث عن معرف الكتاب في بداية المسار
        if (preg_match('/^([^\/]+)\//', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في مجلد extracted_texts
        if (preg_match('/extracted_texts\/([^\/]+)/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في مجلد processed_texts
        if (preg_match('/processed_texts\/([^\/]+)/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في مجلد uploads
        if (preg_match('/uploads\/([^\/]+)/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في اسم الملف مباشرة (للملفات في المجلد الجذر)
        if (preg_match('/^([a-zA-Z0-9_-]+_\d{8}_\d{6})/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في تنسيق الملفات الجديدة: book8308_20250728173825_20250729073953
        if (preg_match('/^([a-zA-Z0-9_-]+_\d{8}_\d{6}_\d{8}_\d{6})/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في تنسيق الملفات الحالي: book6620_20250730180242_20250730220145
        if (preg_match('/^([a-zA-Z0-9_-]+_\d{8}_\d{6}_\d{8}_\d{6})/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في تنسيق الملفات الحالي: pt-2804241-yawum-fi-bait-arrasul_20250731051456
        if (preg_match('/^([a-zA-Z0-9_-]+_\d{8}_\d{6})/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        // البحث في أي معرف كتاب يبدأ بحروف ويحتوي على timestamp
        if (preg_match('/^([a-zA-Z0-9_-]+_\d{8}_\d{6})/', $path, $matches)) {
            $bookId = $matches[1];
            return $bookId;
        }
        
        return '';
    }
    

}
