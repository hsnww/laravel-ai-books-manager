<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImprovedText extends Model
{
    use HasFactory;
    protected $table = 'improved_texts';

    protected $fillable = [
        'book_id',
        'original_file',
        'improved_text',
        'target_language',
        'improvement_type'
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * علاقة مع الكتاب
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
} 