<?php
namespace App\Actions\Comment;

use App\Models\DiscussionComment;
use App\Repositories\DiscussionRepository\DiscussionRepository;

class DeleteCommentAction
{
    public function __construct(
        private CommentRepository $repository
    ) {}

    public function execute(DiscussionComment $comment): bool
    {
        return $this->repository->delete($comment);
    }
}