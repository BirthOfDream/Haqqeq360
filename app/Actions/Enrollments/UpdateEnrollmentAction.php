<?php

namespace App\Actions\Enrollments;

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;

class UpdateEnrollmentAction
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Enrollment $enrollment, array $data): Enrollment
    {
        $user = Auth::user();

        if ($user->id !== $enrollment->user_id && !$user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'unauthorized' => 'You are not allowed to update this enrollment.'
            ]);
        }

        $validator = Validator::make($data, [
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:pending,active,completed'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->update($enrollment, $validator->validated());
    }
}
