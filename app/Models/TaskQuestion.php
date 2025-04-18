<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskQuestion extends Model
{
    protected $fillable = [
        'task_id',
        'question_text',
        'question_type',
        'score',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(TaskOption::class, 'question_id');
    }
}
