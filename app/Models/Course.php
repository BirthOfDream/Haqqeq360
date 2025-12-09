<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'duration_weeks',
        'level',
        'seats',
        'mode',
        'cover_image',
        'status',
        'instructor_id',
        'price',
        'discounted_price',
        'image_path',
    ];

    protected $casts = [
        'duration_weeks' => 'integer',
        'seats' => 'integer',
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    /**
     * Get the instructor (user) for this course
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Alias for instructor() to maintain backward compatibility
     */
    public function user(): BelongsTo
    {
        return $this->instructor();
    }

    /**
     * Get all enrollments for this course
     */
    public function enrollments(): MorphMany
    {
        return $this->morphMany(Enrollment::class, 'enrollable');
    }

    /**
     * Get publish requests for this course
     */
    public function publishRequests()
    {
        return $this->hasMany(CoursePublishRequest::class, 'course_id');
    }

    /**
     * Get all plans associated with this course
     */
    public function plans(): MorphMany
    {
        return $this->morphMany(Plan::class, 'planable');
    }

    /**
     * Get all units for this course
     */
    public function units(): MorphMany
    {
        return $this->morphMany(Unit::class, 'unitable')->orderBy('order');
    }

    /**
     * Get all lessons through units
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Unit::class, 'unitable_id', 'unit_id', 'id', 'id')
            ->where('units.unitable_type', self::class);
    }

    /**
     * Get all assignments for this course through lessons and units
     * Using a custom HasManyThrough-like relationship
     */
    public function assignments()
    {
        return Assignment::query()
            ->select('assignments.*')
            ->join('lessons', 'assignments.lesson_id', '=', 'lessons.id')
            ->join('units', 'lessons.unit_id', '=', 'units.id')
            ->where('units.unitable_type', self::class)
            ->where('units.unitable_id', $this->id);
    }

    /**
     * Get available seats for this course
     */
    public function getAvailableSeatsAttribute(): int
    {
        $enrollmentsCount = $this->enrollments_count ?? $this->enrollments()->count();
        return max(0, $this->seats - $enrollmentsCount);
    }
}