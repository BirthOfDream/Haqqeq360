<?php

namespace App\Actions\Assignments;

use App\Repositories\Interfaces\AssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Assignment;

class ShowAssignmentAction
{
    protected $repo;

    public function __construct(AssignmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($id): Assignment
    {
        return $this->repo->findById($id);
    }
}
