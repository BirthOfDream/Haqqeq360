<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    use HasUuids;

    protected $table = 'programs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'title_ar',
        'title_en',
        'slug',
        'description_ar',
        'description_en',
        'category_id',
        'difficulty_level',
        'delivery_mode',
        'duration_weeks',
        'duration_days',
        'price',
        'currency',
        'cover_image_url',
        'is_published',
        'is_featured',
        'max_participants',
        'current_enrollments',
        'created_by',
        'published_at',
    ];

    // Relationships
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
