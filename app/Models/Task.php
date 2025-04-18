<?php

namespace App\Models;

use App\Models\TaskQuestion;
use App\Models\TaskResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'instructions',
        'duration',
    ];

    public function questions()
    {
        return $this->hasMany(TaskQuestion::class);
    }

    public function results()
    {
        return $this->hasMany(TaskResult::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
