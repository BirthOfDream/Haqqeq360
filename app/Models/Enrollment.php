<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model {
    protected $fillable = ['user_id','enrollable_id','enrollable_type','progress','status'];
    public function user() { return $this->belongsTo(User::class); }
    public function enrollable() { return $this->morphTo(); }
}
