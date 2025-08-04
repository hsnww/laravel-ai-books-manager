<?php

namespace App\Filament\Resources\FileManagerResource\Pages;

use App\Filament\Resources\FileManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use App\Models\FileManager;
use Illuminate\Database\Eloquent\Builder;

class ListFileManagers extends ListRecords
{
    protected static string $resource = FileManagerResource::class;

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('sync_files')
                ->label('مزامنة الملفات')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('تأكيد مزامنة الملفات')
                ->modalDescription('سيتم مسح جميع مجلدات الملفات وتحديث قاعدة البيانات. هل تريد المتابعة؟')
                ->modalSubmitActionLabel('نعم، مزامنة الملفات')
                ->modalCancelActionLabel('إلغاء')
                ->action(function () {
                    try {
                        $fileManagerService = new \App\Services\FileManagerService();
                        $result = $fileManagerService->syncFiles();
                        
                        if ($result['success']) {
                            \Filament\Notifications\Notification::make()
                                ->title('تمت المزامنة بنجاح')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ في المزامنة')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('خطأ في المزامنة')
                            ->body('حدث خطأ أثناء مزامنة الملفات: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('book_folder')
                    ->label('معرف الكتاب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('book_title')
                    ->label('عنوان الكتاب')
                    ->getStateUsing(function ($record) {
                        // البحث عن عنوان الكتاب عبر جدول books
                        $bookInfo = \App\Models\BookInfo::join('books', 'books_info.book_id', '=', 'books.id')
                            ->where('books.book_identify', $record->book_folder)
                            ->select('books_info.title')
                            ->first();
                        return $bookInfo ? $bookInfo->title : 'غير محدد';
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                \Filament\Tables\Columns\TextColumn::make('book_author')
                    ->label('المؤلف')
                    ->getStateUsing(function ($record) {
                        // البحث عن المؤلف عبر جدول books
                        $bookInfo = \App\Models\BookInfo::join('books', 'books_info.book_id', '=', 'books.id')
                            ->where('books.book_identify', $record->book_folder)
                            ->select('books_info.author')
                            ->first();
                        return $bookInfo ? $bookInfo->author : 'غير محدد';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('folder_type')
                    ->label('نوع المجلد')
                    ->getStateUsing(function ($record) {
                        return match ($record->folder) {
                            'extracted_texts' => 'النصوص المستخرجة',
                            'processed_texts' => 'النصوص المعالجة',
                            default => $record->folder,
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'النصوص المستخرجة' => 'success',
                        'النصوص المعالجة' => 'warning',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('file_count')
                    ->label('عدد الملفات')
                    ->getStateUsing(function ($record) {
                        // حساب عدد الملفات في نفس مجلد الكتاب
                        return FileManager::where('folder', $record->folder)
                            ->where('book_folder', $record->book_folder)
                            ->where('path', 'not like', '%backup_%')
                            ->count();
                    })
                    ->badge()
                    ->color('info'),
                \Filament\Tables\Columns\TextColumn::make('total_size')
                    ->label('الحجم الإجمالي')
                    ->getStateUsing(function ($record) {
                        $totalSize = FileManager::where('folder', $record->folder)
                            ->where('book_folder', $record->book_folder)
                            ->where('path', 'not like', '%backup_%')
                            ->sum('size');
                        return number_format($totalSize) . ' bytes';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('last_modified')
                    ->label('آخر تعديل')
                    ->getStateUsing(function ($record) {
                        $latestFile = FileManager::where('folder', $record->folder)
                            ->where('book_folder', $record->book_folder)
                            ->where('path', 'not like', '%backup_%')
                            ->orderBy('modified_at', 'desc')
                            ->first();
                        return $latestFile ? $latestFile->modified_at->format('Y-m-d H:i:s') : '-';
                    })
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('folder')
                    ->label('نوع المجلد')
                    ->options([
                        'extracted_texts' => 'النصوص المستخرجة',
                        'processed_texts' => 'النصوص المعالجة',
                    ])
                    ->default('extracted_texts'),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view_folder')
                    ->label('عرض الملفات')
                    ->icon('heroicon-o-folder-open')
                    ->color('primary')
                    ->url(function ($record) {
                        if ($record->book_folder) {
                            return route('filament.admin.resources.file-managers.folder', ['folder' => $record->book_folder]);
                        }
                        return '#';
                    }),
                
                \Filament\Tables\Actions\Action::make('manage_files')
                    ->label('إدارة الملفات')
                    ->icon('heroicon-o-folder')
                    ->color('success')
                    ->visible(fn ($record) => $record->folder === 'extracted_texts')
                    ->url(function ($record) {
                        if ($record->book_folder) {
                            return route('file-manager.show', ['bookId' => $record->book_folder]);
                        }
                        return '#';
                    })
                    ->openUrlInNewTab(),
                
                \Filament\Tables\Actions\Action::make('ai_processor')
                    ->label('معالجة بالذكاء الاصطناعي')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('warning')
                    ->visible(fn ($record) => $record->folder === 'extracted_texts')
                    ->url(function ($record) {
                        if ($record->book_folder) {
                            return route('ai-processor.show', ['bookId' => $record->book_folder]);
                        }
                        return '#';
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف من قاعدة البيانات'),
                ]),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        // استخدام Eloquent Builder مع تجميع حسب book_folder
        return FileManager::query()
            ->select([
                'folder',
                'book_folder',
                \DB::raw('COUNT(*) as file_count'),
                \DB::raw('SUM(size) as total_size'),
                \DB::raw('MAX(modified_at) as last_modified'),
                \DB::raw('MIN(id) as id'),
                \DB::raw('MIN(path) as path')
            ])
            ->whereIn('folder', ['extracted_texts', 'processed_texts'])
            ->whereNotNull('book_folder')
            ->where('path', 'not like', '%backup_%')
            ->where('path', 'not like', '%uploads%')
            ->groupBy('folder', 'book_folder')
            ->orderBy('last_modified', 'desc');
    }
}
