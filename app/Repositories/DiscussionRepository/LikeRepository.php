<?php

namespace App\Repositories\DiscussionRepository;

use App\Models\DiscussionLike;
use Illuminate\Database\Eloquent\Model;

class LikeRepository
{
    public function toggle(Model $likeable, int $userId): bool
    {
        $like = DiscussionLike::where('user_id', $userId)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->first();

        if ($like) {
            $like->delete();
            return false;
        }

        DiscussionLike::create([
            'user_id' => $userId,
            'likeable_id' => $likeable->id,
            'likeable_type' => get_class($likeable),
        ]);

        return true;
    }

    public function isLiked(Model $likeable, int $userId): bool
    {
        return DiscussionLike::where('user_id', $userId)
            ->where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->exists();
    }

    public function getLikesCount(Model $likeable): int
    {
        return DiscussionLike::where('likeable_id', $likeable->id)
            ->where('likeable_type', get_class($likeable))
            ->count();
    }
}