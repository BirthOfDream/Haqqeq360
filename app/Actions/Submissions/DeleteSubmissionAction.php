<?php

namespace App\Actions\Submissions;

use App\Repositories\SubmissionRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteSubmissionAction
{
    protected $repo;

    public function __construct(SubmissionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($submission)
    {
        if (Auth::id() !== $submission->user_id && !Auth::user()->hasRole('instructor')) {
            throw new AuthorizationException('You are not authorized to delete this submission.');
        }

        return $this->repo->delete($submission);
    }
}
