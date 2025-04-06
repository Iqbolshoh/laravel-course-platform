<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModel extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = ['title', 'description', 'image', 'teacher_id', 'is_published', 'created_at', 'updated_at'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
