<?php

namespace App\Actions\Courses;

use App\Repositories\Interfaces\CourseRepositoryInterface;

class FilterCourseAction
{
    protected CourseRepositoryInterface $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $filters, int $limit = 10)
    {
        return $this->repo->filter($filters, $limit);
    }
}
