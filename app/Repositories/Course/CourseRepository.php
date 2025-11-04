<?php

namespace App\Repositories\Course;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository implements CourseRepositoryInterface
{
    public function all(int $limit = 10)
    {
        return Course::with('instructor')->paginate($limit);
    }

    public function find(int $id): ?Course
    {
        return Course::with(['instructor', 'assignments'])->find($id);
    }

    public function searchByTitle(string $title, int $limit = 10)
    {
        return Course::where('title', 'like', "%$title%")
                     ->with('instructor')
                     ->paginate($limit);
    }

    public function filter(array $filters, int $limit = 10)
    {
        $query = Course::query()->with('instructor');

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (!empty($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        return $query->paginate($limit);
    }
}
