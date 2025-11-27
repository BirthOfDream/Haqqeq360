<?php

namespace App\Actions\Courses;

use App\Models\Course;

class IndexCourseAction
{
    public function execute(int $limit = 10)
    {
        $courses = Course::with(['instructor:id,name,email'])
            ->withCount('enrollments')
            ->where('status', 'published')
            ->select([
                'id',
                'title',
                'slug',
                'description',
                'duration_weeks',
                'level',
                'mode',
                'seats',
                'cover_image',
                'status',
                'instructor_id',
                'created_at',
                'updated_at'
            ])
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