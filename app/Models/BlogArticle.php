<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'original_file',
        'title',
        'article_content',
        'target_language',
        'article_type',
        'word_count',
        'seo_keywords',
        'status',
        'processing_date'
    ];

    protected $casts = [
        'processing_date' => 'datetime',
        'word_count' => 'integer',
    ];

    /**
     * Get the book that owns the blog article
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the book information
     */
    public function bookInfo()
    {
        return $this->hasOne(BookInfo::class, 'book_id', 'book_id');
    }

    /**
     * Scope for published articles
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft articles
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the word count of the article
     */
    public function getWordCountAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Calculate word count from content if not set
        return str_word_count(strip_tags($this->article_content));
    }

    /**
     * Get SEO keywords as array
     */
    public function getSeoKeywordsArrayAttribute()
    {
        if (!$this->seo_keywords) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->seo_keywords));
    }
}
