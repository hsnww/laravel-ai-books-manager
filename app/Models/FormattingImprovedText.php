<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormattingImprovedText extends Model
{
    protected $table = 'formatting_improved_texts';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'title',
        'improved_text',
        'target_language',
        'processing_date',
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that owns this formatting improved text.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
} 