<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function getAllForUser(int $userId);
    public function findById(int $id): ?Notification;
    public function create(array $data): Notification;
    public function createBulk(array $data): Collection; // returns created notifications
    public function markAsRead(Notification $notification): Notification;
    public function markAsUnread(Notification $notification): Notification;
    public function delete(Notification $notification): bool;
}
