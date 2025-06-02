<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'video_url',
        'content',
        'is_preview',
        'order',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    protected static function booted(): void
    {
        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = \Str::slug($lesson->title);
            }
        });

        static::updating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = \Str::slug($lesson->title);
            }
        });
    }
}
