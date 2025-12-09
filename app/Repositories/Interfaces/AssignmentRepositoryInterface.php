<?php

namespace App\Repositories\Interfaces;

use App\Models\Assignment;

interface AssignmentRepositoryInterface
{
    public function getAll();
    public function findById($id): ?Assignment;
    public function create(array $data): Assignment;
    public function update(Assignment $assignment, array $data): Assignment;
    public function delete(Assignment $assignment): bool;
}
