<?php

namespace App\Actions\Assignments;

use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;

class DeleteAssignmentAction
{
    protected $repo;

    public function __construct(AssignmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Assignment $assignment): bool
    {
        $user = Auth::user();

        if (!$user->hasRole('instructor')) {
            throw ValidationException::withMessages(['role' => 'Only instructors can delete assignments.']);
        }

        if ($assignment->course->user_id !== $user->id) {
            throw ValidationException::withMessages(['forbidden' => 'You cannot delete assignments for courses you do not own.']);
        }

        return $this->repo->delete($assignment);
    }
}
