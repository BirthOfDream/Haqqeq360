<?php

namespace App\Actions\Notifications;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ToggleReadNotificationAction
{
    protected NotificationRepositoryInterface $repo;

    public function __construct(NotificationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function markRead(int $id)
    {
        $notification = $this->repo->findById($id);
        if (!$notification) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Notification not found.");
        }

        if ($notification->user_id !== Auth::id()) {
            throw new \Illuminate\Auth\Access\AuthorizationException("You cannot modify this notification.");
        }

        return $this->repo->markAsRead($notification);
    }

    public function markUnread(int $id)
    {
        $notification = $this->repo->findById($id);
        if (!$notification) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Notification not found.");
        }

        if ($notification->user_id !== Auth::id()) {
            throw new \Illuminate\Auth\Access\AuthorizationException("You cannot modify this notification.");
        }

        return $this->repo->markAsUnread($notification);
    }
}
