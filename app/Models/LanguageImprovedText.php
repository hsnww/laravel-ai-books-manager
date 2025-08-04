<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageImprovedText extends Model
{
    protected $table = 'language_improved_texts';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'improved_text',
        'target_language',
        'processing_date',
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that owns this language improved text.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
} 