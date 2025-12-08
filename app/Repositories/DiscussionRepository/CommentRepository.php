<?php 

namespace App\Repositories\DiscussionRepository;

use App\Models\DiscussionComment;

class CommentRepository
{
    public function create(array $data): DiscussionComment
    {
        return DiscussionComment::create($data);
    }

    public function findById(int $id): ?DiscussionComment
    {
        return DiscussionComment::with(['user', 'likes'])->find($id);
    }

    public function update(DiscussionComment $comment, array $data): bool
    {
        return $comment->update($data);
    }

    public function delete(DiscussionComment $comment): bool
    {
        return $comment->delete();
    }
}