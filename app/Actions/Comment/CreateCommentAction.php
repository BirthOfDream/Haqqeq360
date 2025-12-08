<?php
namespace App\Actions\Comment;

use App\Models\DiscussionComment;
use App\Repositories\DiscussionRepository\CommentRepository;

class CreateCommentAction
{
    public function __construct(
        private CommentRepository $repository
    ) {}

    public function execute(array $data): DiscussionComment
    {
        return $this->repository->create($data);
    }
}