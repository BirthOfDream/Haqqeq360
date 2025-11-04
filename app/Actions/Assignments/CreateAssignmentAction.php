<?php

namespace App\Actions\Assignments;

use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\Assignment;
use App\Models\Course;

class CreateAssignmentAction
{
    protected $repo;

    public function __construct(AssignmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data): Assignment
    {
        $user = Auth::user();

        if (!$user->hasRole('instructor')) {
            throw ValidationException::withMessages(['role' => 'Only instructors can create assignments.']);
        }

        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $course = Course::find($data['course_id']);
        if ($course->user_id !== $user->id) {
            throw ValidationException::withMessages(['forbidden' => 'You cannot create assignments for courses you do not own.']);
        }

        return $this->repo->create($validator->validated());
    }
}
