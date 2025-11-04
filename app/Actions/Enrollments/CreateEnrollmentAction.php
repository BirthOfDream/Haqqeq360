<?php

namespace App\Actions\Enrollments;

use App\Repositories\Contracts\EnrollmentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\Enrollment;

class CreateEnrollmentAction
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data): Enrollment
    {
        $user = Auth::user();

        if (!$user->hasRole('student')) {
            throw ValidationException::withMessages([
                'role' => 'Only students can enroll in courses or bootcamps.'
            ]);
        }

        $validator = Validator::make($data, [
            'enrollable_type' => 'required|string|in:App\\Models\\Course,App\\Models\\Bootcamp',
            'enrollable_id'   => 'required|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Prevent enrolling in own course/bootcamp
        $class = $data['enrollable_type'];
        $target = $class::find($data['enrollable_id']);

        if (!$target) {
            throw ValidationException::withMessages(['target' => 'The enrollable item was not found.']);
        }

        if ($target->user_id === $user->id) {
            throw ValidationException::withMessages(['forbidden' => 'You cannot enroll in your own course or bootcamp.']);
        }

        // Check for existing enrollment
        $exists = Enrollment::where('user_id', $user->id)
            ->where('enrollable_id', $data['enrollable_id'])
            ->where('enrollable_type', $data['enrollable_type'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['duplicate' => 'You are already enrolled in this item.']);
        }

        return $this->repo->create([
            'user_id' => $user->id,
            'enrollable_id' => $data['enrollable_id'],
            'enrollable_type' => $data['enrollable_type'],
            'status' => 'active',
        ]);
    }
}
