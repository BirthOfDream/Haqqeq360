<?php

namespace App\Actions\Courses;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateCourseAction
{
    protected $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Course $course, array $data): Course
    {
        $user = Auth::user();

        if ($user->id !== $course->user_id) {
            throw ValidationException::withMessages(['permission' => 'You can only update your own courses.']);
        }

        $validated = validator($data, [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
        ])->validate();

        return $this->repo->update($course, $validated);
    }
}
