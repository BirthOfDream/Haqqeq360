<?php

namespace App\Actions\Courses;

use App\Repositories\Interfaces\CourseRepositoryInterface;

class IndexCourseAction
{
    protected CourseRepositoryInterface $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $limit = 10)
    {
        return $this->repo->all($limit);
    }
}
