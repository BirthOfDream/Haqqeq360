<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DiscussionThread extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'content',
        'is_pinned',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    protected $with = ['user', 'course'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'thread_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(DiscussionLike::class, 'likeable');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DiscussionNotification::class, 'thread_id');
    }

    // Helper Methods
    public function close(User $user): void
    {
        $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);
    }

    public function open(): void
    {
        $this->update([
            'is_closed' => false,
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    public function togglePin(): void
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function likesCount(): int
    {
        return $this->likes()->count();
    }

    public function repliesCount(): int
    {
        return $this->replies()->count();
    }

    // Scopes
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }
}
