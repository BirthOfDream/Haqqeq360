<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class DiscussionReply extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'parent_id',
        'content',
        'is_moderated',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'is_moderated' => 'boolean',
        'moderated_at' => 'datetime',
    ];

    protected $with = ['user'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'parent_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(DiscussionLike::class, 'likeable');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Helper Methods
    public function moderate(User $user, string $reason): void
    {
        $this->update([
            'is_moderated' => true,
            'moderation_reason' => $reason,
            'moderated_by' => $user->id,
            'moderated_at' => now(),
        ]);
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function likesCount(): int
    {
        return $this->likes()->count();
    }

    // Scopes
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeModerated($query)
    {
        return $query->where('is_moderated', true);
    }
}
