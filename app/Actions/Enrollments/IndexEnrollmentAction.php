<?php

namespace App\Actions\Enrollments;

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class IndexEnrollmentAction
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        $user = Auth::user();

        // Admins see all enrollments, users see their own
        if ($user->hasRole('admin')) {
            return $this->repo->all();
        }

        return $user->enrollments()->with('enrollable')->paginate(10);
    }
}
