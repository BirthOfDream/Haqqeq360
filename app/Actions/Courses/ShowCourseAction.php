<?php

namespace App\Actions\Courses;

use App\Repositories\Interfaces\CourseRepositoryInterface;

class ShowCourseAction
{
    protected CourseRepositoryInterface $repo;

    public function __construct(CourseRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        return $this->repo->find($id);
    }
}
