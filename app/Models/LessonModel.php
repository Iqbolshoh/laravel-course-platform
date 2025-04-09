<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonModel extends Model
{
    use HasFactory;

    protected $table = 'lessons';

    protected $fillable = [
        'course_id',
        'title',
        'video_url',
        'content',
        'order',
    ];

    public function course()
    {
        return $this->belongsTo(CourseModel::class, 'course_id');
    }
}
