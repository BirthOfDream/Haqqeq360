<?php

namespace App\Actions\Enrollments;

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;

class DeleteEnrollmentAction
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Enrollment $enrollment): bool
    {
        $user = Auth::user();

        if ($user->id !== $enrollment->user_id && !$user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'unauthorized' => 'You cannot delete this enrollment.'
            ]);
        }

        return $this->repo->delete($enrollment);
    }
}
