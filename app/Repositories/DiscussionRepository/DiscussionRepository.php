<?php

// app/Repositories/DiscussionRepository.php

namespace App\Repositories\DiscussionRepository;

use App\Models\Discussion;
use App\Models\DiscussionComment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscussionRepository
{
    public function getPublishedDiscussions(int $perPage = 15): LengthAwarePaginator
    {
        return Discussion::with(['user', 'likes'])
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllDiscussions(int $perPage = 15): LengthAwarePaginator
    {
        return Discussion::with(['user', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Discussion
    {
        return Discussion::with(['user', 'comments.user', 'comments.likes', 'likes'])
            ->find($id);
    }

    public function create(array $data): Discussion
    {
        return Discussion::create($data);
    }

    public function update(Discussion $discussion, array $data): bool
    {
        return $discussion->update($data);
    }

    public function delete(Discussion $discussion): bool
    {
        return $discussion->delete();
    }

    public function getCommentsByDiscussion(int $discussionId): Collection
    {
        return DiscussionComment::with(['user', 'likes', 'replies.user', 'replies.likes'])
            ->where('discussion_id', $discussionId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}