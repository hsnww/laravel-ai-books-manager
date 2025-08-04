<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Book extends Model
{
    use HasFactory;
    
    protected $table = 'books';
    
    protected $fillable = [
        'book_identify',
        'user_id',
        'book_language',
        'file_path',
        'file_size',
        'pages_count',
        'extraction_time'
    ];

    protected $casts = [
        'extraction_time' => 'float',
        'file_size' => 'integer',
        'pages_count' => 'integer'
    ];

    /**
     * علاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة مع معلومات الكتاب
     */
    public function bookInfo(): HasOne
    {
        return $this->hasOne(BookInfo::class);
    }

    /**
     * علاقة مع النصوص المحسنة
     */
    public function enhancedTexts()
    {
        return $this->hasMany(EnhancedText::class);
    }

    /**
     * علاقة مع النصوص المترجمة
     */
    public function translatedTexts()
    {
        return $this->hasMany(TranslatedText::class);
    }

    /**
     * علاقة مع النصوص الملخصة
     */
    public function summarizedTexts()
    {
        return $this->hasMany(SummarizedText::class);
    }
}
