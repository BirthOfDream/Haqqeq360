<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;

class SearchBootcampAction
{
    public function execute(string $searchTerm, int $limit = 10)
    {
        $bootcamps = Bootcamp::with(['instructor:id,name,email'])
            ->withCount('enrollments')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            })
            ->paginate($limit);

        // Add available seats to each bootcamp
        $bootcamps->getCollection()->transform(function ($bootcamp) {
            $bootcamp->available_seats = max(0, $bootcamp->seats - $bootcamp->enrollments_count);
            $bootcamp->is_fully_booked = $bootcamp->available_seats === 0;
            return $bootcamp;
        });

        return $bootcamps;
    }
}