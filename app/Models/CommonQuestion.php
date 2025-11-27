<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonQuestion extends Model
{
    protected $table = 'common_questions';
    protected $fillable = [
        'question',
        'answer',
        'status',
    ];
}
