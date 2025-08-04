<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnhancedText extends Model
{
    protected $table = 'enhanced_texts';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'title',
        'enhanced_text',
        'target_language',
        'processing_date',
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that owns this enhanced text.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
