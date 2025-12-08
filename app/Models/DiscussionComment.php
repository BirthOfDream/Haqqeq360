<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionComment::class, 'parent_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(DiscussionLike::class, 'likeable');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }
}