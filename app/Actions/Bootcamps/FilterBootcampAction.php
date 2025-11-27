<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;

class FilterBootcampAction
{
    public function execute(array $filters, int $limit = 10)
    {
        $query = Bootcamp::with(['instructor:id,name,email'])
            ->withCount('enrollments');

        // Apply filters
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        if (isset($filters['certificate'])) {
            $query->where('certificate', filter_var($filters['certificate'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['has_seats']) && filter_var($filters['has_seats'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereRaw('seats > (SELECT COUNT(*) FROM enrollments WHERE enrollments.enrollable_id = bootcamps.id AND enrollments.enrollable_type = ?)', ['App\\Models\\Bootcamp']);
        }

        $bootcamps = $query->paginate($limit);

        // Add available seats to each bootcamp
        $bootcamps->getCollection()->transform(function ($bootcamp) {
            $bootcamp->available_seats = max(0, $bootcamp->seats - $bootcamp->enrollments_count);
            $bootcamp->is_fully_booked = $bootcamp->available_seats === 0;
            return $bootcamp;
        });

        return $bootcamps;
    }
}