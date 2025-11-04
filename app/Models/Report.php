<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    protected $fillable = ['user_id','enrollable_type','enrollable_id','completion_rate','grade_avg','feedback_summary'];
    public function user() { return $this->belongsTo(User::class); }
}
