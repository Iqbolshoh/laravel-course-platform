<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'is_published',
        'price',
        'discount',
        'teacher_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    protected static function booted()
    {
        static::creating(function ($course) {
            $course->slug = str($course->title)->slug();
        });

        static::updating(function ($course) {
            if ($course->isDirty('title')) {
                $course->slug = str($course->title)->slug();
            }

            if ($course->isDirty('image')) {
                $originalImage = $course->getOriginal('image');
                if ($originalImage && Storage::disk('public')->exists($originalImage)) {
                    Storage::disk('public')->delete($originalImage);
                }
            }
        });

        static::deleting(function ($course) {
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
        });
    }
}
