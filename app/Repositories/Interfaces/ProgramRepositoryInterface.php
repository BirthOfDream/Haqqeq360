<?php

namespace App\Repositories\Interfaces;

use App\Models\Program;

interface ProgramRepositoryInterface
{
    public function all(array $filters = [], int $limit = 10, int $page = 1);
    public function findById(string $id): ?Program;
    public function findBySlug(string $slug): ?Program;
}
