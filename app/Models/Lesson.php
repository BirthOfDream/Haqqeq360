<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'unit_id',
        'title',
        'content',
        'order',
        'video_url',
        'resource_link',
        'attachment_path',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Lesson has many assignments
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Get the course through unit
    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            Unit::class,
            'id', // Foreign key on units table
            'id', // Foreign key on courses table
            'unit_id', // Local key on lessons table
            'unitable_id' // Local key on units table
        )->where('units.unitable_type', Course::class);
    }
}
