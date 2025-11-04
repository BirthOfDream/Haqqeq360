<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model {
    protected $fillable = ['title','description','due_date','course_id'];
    public function course() { return $this->belongsTo(Course::class); }
    public function submissions() { return $this->hasMany(Submission::class); }
}

