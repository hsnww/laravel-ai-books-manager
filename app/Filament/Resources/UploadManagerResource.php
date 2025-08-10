<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UploadManagerResource\Pages;
use App\Models\FileManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\BookProcessor;
use App\Services\PdfTextExtractor;
use Illuminate\Support\Facades\Storage;

class UploadManagerResource extends Resource
{
    protected static ?string $model = FileManager::class;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): string
    {
        return __('File Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Upload Files');
    }

    public static function getModelLabel(): string
    {
        return __('Uploaded File');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Uploaded Files');
    }

    // إعدادات مخصصة لرفع الملفات
    protected static function getUploadMaxFileSize(): int
    {
        return 50 * 1024; // 50MB in KB
    }

    protected static function getUploadAcceptedFileTypes(): array
    {
        return ['application/pdf'];
    }

    // تجاوز إعدادات Filament الافتراضية
    protected static function getUploadValidationRules(): array
    {
        return [
            'file_path' => [
                'required',
                'file',
                'mimes:pdf',
                'max:' . (50 * 1024), // 50MB in KB
            ],
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label(__('Choose PDF File'))
                    ->acceptedFileTypes(static::getUploadAcceptedFileTypes())
                    ->required()
                    ->helperText(__('You can upload PDF files with a maximum size of 50 megabytes'))
                    ->storeFileNamesIn('original_name')
                    ->disk('public')
                    ->directory('temp')
                    ->visibility('public')
                    ->preserveFilenames(false)
                    ->maxFiles(1)
                    ->minSize(1)
                    ->maxSize(static::getUploadMaxFileSize()), // استخدام الإعداد المخصص
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('folder', 'uploads'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('File Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size')
                    ->label(__('File Size'))
                    ->formatStateUsing(fn ($state) => number_format($state) . ' bytes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('File Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'txt' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('folder')
                    ->label(__('File Folder'))
                    ->badge(),
                Tables\Columns\TextColumn::make('modified_at')
                    ->label(__('Last Modified'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('File Type Filter'))
                    ->options([
                        'pdf' => 'PDF',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // إجراء استخراج النصوص
                Action::make('extract_text')
                    ->label(__('Extract Texts'))
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (FileManager $record) => $record->type === 'pdf')
                    ->action(function (FileManager $record) {
                        try {
                            $pdfExtractor = new PdfTextExtractor();
                            $processor = new BookProcessor($pdfExtractor);
                            $result = $processor->processBook($record);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title(__('Text extraction completed successfully'))
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('Failed to extract texts'))
                                    ->body($result['error'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Text extraction error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                // إجراء عرض PDF
                Action::make('view_pdf')
                    ->label(__('View PDF'))
                    ->icon('heroicon-o-document')
                    ->color('primary')
                    ->visible(fn (FileManager $record) => $record->type === 'pdf')
                    ->url(fn (FileManager $record) => route('pdf-viewer.show', $record->id))
                    ->openUrlInNewTab(),
                

                

                

                
                // حذف مع حذف الملف الفعلي
                Tables\Actions\DeleteAction::make('delete_with_file')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (FileManager $record) {
                        try {
                            // حذف الملف الفعلي
                            $filePath = storage_path('app/public/' . $record->path);
                            
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                            
                            // حذف من قاعدة البيانات
                            $record->delete();
                            
                            Notification::make()
                                ->title(__('File deleted successfully'))
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('Error deleting file'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // حذف عادي (من قاعدة البيانات فقط)
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف من قاعدة البيانات'),
                    
                    // حذف مع حذف الملف الفعلي
                    Tables\Actions\BulkAction::make('delete_with_file')
                        ->label(__('Delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function ($records) {
                            $deletedCount = 0;
                            $errors = [];
                            
                            foreach ($records as $record) {
                                try {
                                    // حذف الملف الفعلي
                                    $filePath = storage_path('app/public/' . $record->path);
                                    
                                    if (file_exists($filePath)) {
                                        unlink($filePath);
                                    }
                                    
                                    // حذف من قاعدة البيانات
                                    $record->delete();
                                    $deletedCount++;
                                    
                                } catch (\Exception $e) {
                                    $errors[] = __("Error deleting file") . " {$record->name}: " . $e->getMessage();
                                }
                            }
                            
                            $message = __("Files deleted") . " {$deletedCount} " . __("files successfully");
                            if (!empty($errors)) {
                                $message .= "\n" . implode("\n", $errors);
                            }
                            
                            Notification::make()
                                ->title(__('Files deleted'))
                                ->body($message)
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListUploadManagers::route('/'),
            'create' => Pages\CreateUploadManager::route('/create'),
        ];
    }
} 