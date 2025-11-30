<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'enrollable_type',
        'enrollable_id',
        'progress',
        'status'
    ];

    protected $casts = [
        'progress' => 'decimal:2'
    ];

    // Polymorphic relationship
    public function enrollable()
    {
        return $this->morphTo();
    }

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
