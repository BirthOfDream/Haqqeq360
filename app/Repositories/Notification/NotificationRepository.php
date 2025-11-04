<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class NotificationRepository implements NotificationRepositoryInterface
{
    public function getAllForUser(int $userId)
    {
        return Notification::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    public function findById(int $id): ?Notification
    {
        return Notification::find($id);
    }

    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function createBulk(array $data): Collection
    {
        // $data is array of arrays
        $created = collect();
        foreach ($data as $item) {
            $created->push(Notification::create($item));
        }
        return $created;
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->is_read = true;
        $notification->save();
        return $notification;
    }

    public function markAsUnread(Notification $notification): Notification
    {
        $notification->is_read = false;
        $notification->save();
        return $notification;
    }

    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }
    public function bulkInsert(array $data)
{
    return \DB::table('notifications')->insert($data);
}
}
