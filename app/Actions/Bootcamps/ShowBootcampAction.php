<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;

class ShowBootcampAction
{
    public function execute(int $id)
    {
        $bootcamp = Bootcamp::with([
            'instructor:id,name,email',
            'enrollments.user:id,name,email'
        ])
            ->withCount('enrollments')
            ->find($id);

        if (!$bootcamp) {
            return null;
        }

        // Add available seats information
        $bootcamp->available_seats = max(0, $bootcamp->seats - $bootcamp->enrollments_count);
        $bootcamp->is_fully_booked = $bootcamp->available_seats === 0;
        $bootcamp->enrollment_percentage = $bootcamp->seats > 0 
            ? round(($bootcamp->enrollments_count / $bootcamp->seats) * 100, 2)
            : 0;

        return $bootcamp;
    }
}