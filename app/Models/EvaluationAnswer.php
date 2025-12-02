<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnswer extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'answer_value',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(EvaluationResponse::class, 'response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }
}