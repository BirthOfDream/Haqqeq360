<?php

namespace App\Repositories;

use App\Models\Enrollment;
use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function getAll()
    {
        return Enrollment::with(['user', 'enrollable'])->get();
    }

    public function findById($id): ?Enrollment
    {
        return Enrollment::with(['user', 'enrollable'])->findOrFail($id);
    }

    public function create(array $data): Enrollment
    {
        return Enrollment::create($data);
    }

    public function update(Enrollment $enrollment, array $data): Enrollment
    {
        $enrollment->update($data);
        return $enrollment;
    }

    public function delete(Enrollment $enrollment): bool
    {
        return $enrollment->delete();
    }
}
