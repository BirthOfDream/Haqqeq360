<?php

namespace App\Actions\Courses;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateCourseAction
{
    protected $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data): Course
    {
        $user = Auth::user();

        if (!$user->hasRole('instructor')) {
            throw ValidationException::withMessages(['role' => 'Only instructors can create courses.']);
        }

        $validated = validator($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ])->validate();

        $validated['user_id'] = $user->id;

        return $this->repo->create($validated);
    }
}
