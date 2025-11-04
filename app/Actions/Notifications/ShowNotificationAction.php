<?php

namespace App\Actions\Notifications;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ShowNotificationAction
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
            throw new ModelNotFoundException("Notification not found.");
        }

        // only owner can view
        if ($notification->user_id !== Auth::id()) {
            throw new \Illuminate\Auth\Access\AuthorizationException("You cannot view this notification.");
        }

        return $notification;
    }
}
