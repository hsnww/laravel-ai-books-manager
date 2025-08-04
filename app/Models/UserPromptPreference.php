<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPromptPreference extends Model
{
    protected $table = 'user_prompt_preferences';
    
    protected $fillable = [
        'user_id',
        'preferred_language',
        'default_prompt_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the default prompt for this user.
     */
    public function defaultPrompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class, 'default_prompt_id');
    }
} 