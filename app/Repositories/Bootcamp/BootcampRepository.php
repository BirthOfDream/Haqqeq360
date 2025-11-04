<?php

namespace App\Repositories\Bootcamp;

use App\Models\Bootcamp;
use App\Repositories\Interfaces\BootcampRepositoryInterface;

class BootcampRepository implements BootcampRepositoryInterface
{
    public function allBasic(int $limit = 10)
    {
        return Bootcamp::select('id', 'title', 'duration_weeks', 'description', 'mode')
            ->with('instructor:id,name,email') // include instructor minimal info
            ->paginate($limit);
    }

    public function find(int $id): ?Bootcamp
    {
        return Bootcamp::with(['instructor', 'assignments'])->find($id);
    }

    public function findByTitle(string $title)
    {
        return Bootcamp::where('title', 'like', "%$title%")
            ->with('instructor')
            ->paginate(10);
    }

    public function create(array $data): Bootcamp
    {
        return Bootcamp::create($data);
    }
}
