<?php
// ==================================================
// File: app/Models/Submission.php
// ==================================================

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
        'submission_text',
        'attachment_path',
        'file_url',
        'score',
        'feedback',
        'grade',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'decimal:2',
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

    // Submission belongs to a student (user)
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias for backward compatibility
    public function user()
    {
        return $this->student();
    }
}