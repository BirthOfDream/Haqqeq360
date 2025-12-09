<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'due_date',
        'max_score',
        'attachment_path',
        'published',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_score' => 'decimal:2',
        'published' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Assignment belongs to a course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Assignment belongs to a lesson
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // Assignment has many submissions
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}