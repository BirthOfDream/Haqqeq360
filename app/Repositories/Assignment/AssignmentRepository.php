<?php

namespace App\Repositories\Assignment;

use App\Models\Assignment;
use App\Repositories\Interfaces\AssignmentRepositoryInterface;

class AssignmentRepository implements AssignmentRepositoryInterface
{
    public function getAll()
    {
        return Assignment::with('course')->get();
    }

    public function findById($id): ?Assignment
    {
        return Assignment::with('course')->findOrFail($id);
    }

    public function create(array $data): Assignment
    {
        return Assignment::create($data);
    }

    public function update(Assignment $assignment, array $data): Assignment
    {
        $assignment->update($data);
        return $assignment;
    }

    public function delete(Assignment $assignment): bool
    {
        return $assignment->delete();
    }
}
