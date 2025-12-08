<?php

// app/Actions/Discussion/CreateDiscussionAction.php

namespace App\Actions\Discussion;

use App\Models\Discussion;
use App\Repositories\DiscussionRepository\DiscussionRepository;
use Illuminate\Support\Facades\Storage;

class CreateDiscussionAction
{
    public function __construct(
        private DiscussionRepository $repository
    ) {}

    public function execute(array $data): Discussion
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('discussions', 'public');
        }

        if (!isset($data['is_published'])) {
            $data['is_published'] = false;
        }

        if ($data['is_published'] && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $this->repository->create($data);
    }
}