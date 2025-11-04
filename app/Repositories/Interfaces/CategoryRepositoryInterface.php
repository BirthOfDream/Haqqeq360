<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function all(array $filters = [], int $limit = 10, int $page = 1);
    public function findById(string $id): ?Category;
    public function findBySlug(string $slug): ?Category;
}
