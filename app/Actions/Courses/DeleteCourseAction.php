<?php

namespace App\Actions\Courses;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteCourseAction
{
    protected $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Course $course): bool
    {
        $user = Auth::user();

        if ($user->id !== $course->user_id && !$user->hasRole('admin')) {
            throw ValidationException::withMessages(['permission' => 'You are not allowed to delete this course.']);
        }

        return $this->repo->delete($course);
    }
}
