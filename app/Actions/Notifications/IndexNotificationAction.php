<?php

namespace App\Actions\Notifications;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class IndexNotificationAction
{
    protected NotificationRepositoryInterface $repo;

    public function __construct(NotificationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        $user = Auth::user();
        return $this->repo->getAllForUser($user->id);
    }
}
