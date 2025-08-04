<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslatedText extends Model
{
    protected $table = 'translated_texts';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'title',
        'translated_text',
        'source_language',
        'target_language',
        'processing_date',
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that owns this translated text.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
