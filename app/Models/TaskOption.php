<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TaskQuestion::class, 'question_id');
    }
}
