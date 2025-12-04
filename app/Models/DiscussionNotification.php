<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionNotification extends Model
{
    protected $fillable = [
        'user_id',
        'thread_id',
        'reply_id',
        'type',
        'message',
        'is_read',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'reply_id');
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markEmailSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
        ]);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopePendingEmail($query)
    {
        return $query->where('email_sent', false);
    }
}