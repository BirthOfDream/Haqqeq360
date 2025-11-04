<?php

namespace App\Repositories\Interfaces;

use App\Models\Bootcamp;

interface BootcampRepositoryInterface
{
    /**
     * Get all bootcamps with minimal info (paginated)
     */
    public function allBasic(int $limit = 10);

    /**
     * Find full bootcamp details by ID
     */
    public function find(int $id): ?Bootcamp;

    /**
     * Find bootcamp by title (optional)
     */
    public function findByTitle(string $title);

    /**
     * Create a new bootcamp
     */
    public function create(array $data): Bootcamp;
}

