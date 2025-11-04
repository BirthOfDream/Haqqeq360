<?php

namespace App\Actions\Submissions;

use App\Repositories\SubmissionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowSubmissionAction
{
    protected $repo;

    public function __construct(SubmissionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        $submission = $this->repo->findById($id);

        if (!$submission) {
            throw new ModelNotFoundException('Submission not found.');
        }

        return $submission;
    }
}
