<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessingHistory extends Model
{
    protected $table = 'processing_history';
    
    protected $fillable = [
        'book_id',
        'original_file',
        'processing_type',
        'target_language',
        'processing_options',
        'processing_status',
        'error_message',
        'processing_time_seconds',
    ];

    protected $casts = [
        'processing_options' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the book that owns this processing history.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope a query to only include successful processing.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('processing_status', 'success');
    }

    /**
     * Scope a query to only include failed processing.
     */
    public function scopeFailed($query)
    {
        return $query->where('processing_status', 'failed');
    }

    /**
     * Scope a query to only include in progress processing.
     */
    public function scopeInProgress($query)
    {
        return $query->where('processing_status', 'in_progress');
    }

    /**
     * Scope a query to filter by processing type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('processing_type', $type);
    }
} 