<?php

namespace App\Repositories\Contracts;

use App\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function getAll();
    public function findById($id): ?Enrollment;
    public function create(array $data): Enrollment;
    public function update(Enrollment $enrollment, array $data): Enrollment;
    public function delete(Enrollment $enrollment): bool;
}
