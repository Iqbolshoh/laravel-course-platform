<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'instructions',
        'duration'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(TaskQuestion::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(TaskResult::class);
    }
}