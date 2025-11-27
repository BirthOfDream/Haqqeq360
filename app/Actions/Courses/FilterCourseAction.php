<?php

namespace App\Actions\Courses;

use App\Models\Course;

class FilterCourseAction
{
    public function execute(array $filters, int $limit = 10)
    {
        $query = Course::with(['instructor:id,name,email'])
            ->withCount('enrollments')
            ->where('status', 'published');

        // Apply filters
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        if (isset($filters['has_seats']) && filter_var($filters['has_seats'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereRaw('seats > (SELECT COUNT(*) FROM enrollments WHERE enrollments.enrollable_id = courses.id AND enrollments.enrollable_type = ?)', ['App\\Models\\Course']);
        }

        $courses = $query->paginate($limit);

        // Add available seats to each course
        $courses->getCollection()->transform(function ($course) {
            $course->available_seats = max(0, $course->seats - $course->enrollments_count);
            $course->is_fully_booked = $course->available_seats === 0;
            return $course;
        });

        return $courses;
    }
}