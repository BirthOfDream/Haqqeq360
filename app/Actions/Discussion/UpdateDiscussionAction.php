<?php

namespace App\Actions\Discussion;

use App\Models\Discussion;
use App\Repositories\DiscussionRepository\DiscussionRepository;
use Illuminate\Support\Facades\Storage;

class UpdateDiscussionAction
{
    public function __construct(
        private DiscussionRepository $repository
    ) {}

    public function execute(Discussion $discussion, array $data): bool
    {
        if (isset($data['image']) && is_object($data['image'])) {
            if ($discussion->image) {
                Storage::disk('public')->delete($discussion->image);
            }
            $data['image'] = $data['image']->store('discussions', 'public');
        }

        if ($data['is_published'] && !$discussion->published_at) {
            $data['published_at'] = now();
        }

        return $this->repository->update($discussion, $data);
    }
}