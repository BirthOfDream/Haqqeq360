<?php

namespace App\Actions\Assignments;

use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;

class UpdateAssignmentAction
{
    protected $repo;

    public function __construct(AssignmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Assignment $assignment, array $data): Assignment
    {
        $user = Auth::user();

        if (!$user->hasRole('instructor')) {
            throw ValidationException::withMessages(['role' => 'Only instructors can update assignments.']);
        }

        if ($assignment->course->user_id !== $user->id) {
            throw ValidationException::withMessages(['forbidden' => 'You cannot update assignments for courses you do not own.']);
        }

        $validator = Validator::make($data, [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->update($assignment, $validator->validated());
    }
}
