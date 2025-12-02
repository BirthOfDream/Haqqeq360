<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_type',
        'product_id',
        'product_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    public function loadStandardQuestions(): array
    {
        return [
            [
                'question_text' => 'ما مدى رضاك عن محتوى الدورة؟',
                'question_type' => 'scale',
                'options' => ['غير راضِ أبداً', 'غير راضِ', 'محايد', 'راضِ', 'راضِ جداً'],
                'order' => 1,
            ],
            [
                'question_text' => 'هل كانت الدورة تفي توقعاتك؟',
                'question_type' => 'yes_no',
                'options' => ['لست متأكداً', 'لا', 'نعم'],
                'order' => 2,
            ],
            [
                'question_text' => 'قدم المدرب الدورة بطريقة سهلة وواضحة',
                'question_type' => 'scale',
                'options' => ['غير موافق أبداً', 'غير موافق', 'محايد', 'موافق', 'موافق بشدة'],
                'order' => 3,
            ],
            [
                'question_text' => 'تقييم الأداء العام للمدرب',
                'question_type' => 'grade',
                'options' => ['مقبول', 'جيد', 'جيد جداً', 'ممتاز', 'ممتاز جداً'],
                'order' => 4,
            ],
            [
                'question_text' => 'هل حققت أهدافك من التعلم؟',
                'question_type' => 'scale',
                'options' => ['غير موافق أبداً', 'غير موافق', 'محايد', 'موافق', 'موافق بشدة'],
                'order' => 5,
            ],
            [
                'question_text' => 'هل ترى أن سعر الدورة عادل؟',
                'question_type' => 'scale',
                'options' => ['غير موافق أبداً', 'غير موافق', 'محايد', 'موافق', 'موافق بشدة'],
                'order' => 6,
            ],
            [
                'question_text' => 'تقييمك العام النهائي للدورة',
                'question_type' => 'rating',
                'options' => null,
                'order' => 7,
            ],
            [
                'question_text' => 'ما اقتراحاتك لتحسين جودة التعلم؟',
                'question_type' => 'text',
                'options' => null,
                'order' => 8,
            ],
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProduct($query, string $type, int $id)
    {
        return $query->where('product_type', $type)
                    ->where('product_id', $id);
    }
}