<?php

namespace App\Actions\Enrollments;

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Enrollment;

class ShowEnrollmentAction
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($id): Enrollment
    {
        return $this->repo->findById($id);
    }
}
