<?php
namespace App\Actions\Like;

use App\Repositories\DiscussionRepository\LikeRepository;
use Illuminate\Database\Eloquent\Model;

class ToggleLikeAction
{
    public function __construct(
        private LikeRepository $repository
    ) {}

    public function execute(Model $likeable, int $userId): bool
    {
        return $this->repository->toggle($likeable, $userId);
    }
}