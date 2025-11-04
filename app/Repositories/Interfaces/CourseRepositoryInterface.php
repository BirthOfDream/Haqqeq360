<?php

namespace App\Repositories\Interfaces;

use App\Models\Course;

interface CourseRepositoryInterface
{
    /**
     * Get all courses (paginated)
     */
    public function all(int $limit = 10);

    /**
     * Find a course by ID
     */
    public function find(int $id): ?Course;

    /**
     * Search courses by title
     */
    public function searchByTitle(string $title, int $limit = 10);

    /**
     * Filter courses by level or mode
     */
    public function filter(array $filters, int $limit = 10);
}
