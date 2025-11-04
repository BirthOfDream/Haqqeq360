<?php

namespace App\Actions\Notifications;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class DeleteNotificationAction
{
    protected NotificationRepositoryInterface $repo;

    public function __construct(NotificationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        $notification = $this->repo->findById($id);
        if (!$notification) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Notification not found.");
        }

        if ($notification->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException("You cannot delete this notification.");
        }

        return $this->repo->delete($notification);
    }
}
