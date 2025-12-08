<?php
namespace App\Actions\Discussion;

use App\Models\Discussion;
use App\Repositories\DiscussionRepository\DiscussionRepository;
use Illuminate\Support\Facades\Storage;

class DeleteDiscussionAction
{
    public function __construct(
        private DiscussionRepository $repository
    ) {}

    public function execute(Discussion $discussion): bool
    {
        if ($discussion->image) {
            Storage::disk('public')->delete($discussion->image);
        }

        return $this->repository->delete($discussion);
    }
}