<?php

namespace App\Actions\Courses;

use App\Models\Course;

class ShowCourseAction
{
    public function execute(int $id)
    {
        $course = Course::with([
            'instructor:id,name,email',
            'assignments:id,course_id,title,description,due_date',
            'enrollments.user:id,name,email'
        ])
            ->withCount(['enrollments', 'units'])
            ->where('status', 'published')
            ->find($id);

        if (!$course) {
            return null;
        }

        // Add available seats information
        $course->available_seats = max(0, $course->seats - $course->enrollments_count);
        $course->is_fully_booked = $course->available_seats === 0;
        $course->enrollment_percentage = $course->seats > 0 
            ? round(($course->enrollments_count / $course->seats) * 100, 2)
            : 0;

        return $course;
    }
}