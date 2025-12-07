<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Course extends Model {

    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'title','slug','description','duration_weeks','level',
        'seats','mode','cover_image','status','instructor_id'
    ];

    public function instructor() {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments() {
        return $this->morphMany(Enrollment::class, 'enrollable');
    }

    public function publishRequests() {
        return $this->hasMany(CoursePublishRequest::class);
    }

    public function plans(): MorphMany {
        return $this->morphMany(Plan::class, 'planable');
    }

    public function units() {
        return $this->morphMany(Unit::class, 'unitable')->orderBy('order');
    }

    // The correct assignment relation
    public function assignments()
    {
        return Assignment::whereIn('lesson_id', function ($q) {
            $q->select('id')
              ->from('lessons')
              ->whereIn('unit_id', function ($q2) {
                  $q2->select('id')
                     ->from('units')
                     ->where('unitable_id', $this->id)
                     ->where('unitable_type', self::class);
              });
        });
    }
}