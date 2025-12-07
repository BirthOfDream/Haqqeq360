<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_url',
        'grade',
        'submitted_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Submission belongs to an assignment
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Submission belongs to a student
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
