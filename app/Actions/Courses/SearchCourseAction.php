<?php

namespace App\Actions\Courses;

use App\Models\Course;

class SearchCourseAction
{
    public function execute(string $title, int $limit = 10)
    {
        $courses = Course::with(['instructor:id,name,email'])
            ->withCount('enrollments')
            ->where('status', 'published')
            ->where(function ($query) use ($title) {
                $query->where('title', 'like', "%{$title}%")
                    ->orWhere('description', 'like', "%{$title}%");
            })
            ->paginate($limit);

        // Add available seats to each course
        $courses->getCollection()->transform(function ($course) {
            $course->available_seats = max(0, $course->seats - $course->enrollments_count);
            $course->is_fully_booked = $course->available_seats === 0;
            return $course;
        });

        return $courses;
    }
}