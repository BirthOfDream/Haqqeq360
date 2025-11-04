<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model {
    protected $fillable = ['assignment_id','user_id','file_url','grade','feedback','submitted_at'];
    public function user() { return $this->belongsTo(User::class); }
    public function assignment() { return $this->belongsTo(Assignment::class); }
}
