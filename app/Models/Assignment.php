<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'lesson_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
