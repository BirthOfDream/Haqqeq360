<?php

namespace App\Actions\Courses;

use App\Repositories\Interfaces\CourseRepositoryInterface;

class SearchCourseAction
{
    protected CourseRepositoryInterface $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $title, int $limit = 10)
    {
        return $this->repo->searchByTitle($title, $limit);
    }
}
