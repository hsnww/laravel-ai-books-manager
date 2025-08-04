<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummarizedText extends Model
{
    protected $table = 'summarized_texts';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'title',
        'summarized_text',
        'target_language',
        'summary_length',
        'processing_date',
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that owns this summarized text.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
