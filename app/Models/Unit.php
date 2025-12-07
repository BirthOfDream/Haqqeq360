<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'order',
        'unitable_id',
        'unitable_type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Polymorphic: unit belongs to course, bootcamp or workshop
    public function unitable()
    {
        return $this->morphTo();
    }

    // Unit has many lessons
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
