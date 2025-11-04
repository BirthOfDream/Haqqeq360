<?php

namespace App\Actions\Submissions;

use App\Repositories\SubmissionRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateSubmissionAction
{
    protected $repo;

    public function __construct(SubmissionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute($submission, array $data)
    {
        if (Auth::id() !== $submission->user_id && !Auth::user()->hasRole('instructor')) {
            throw new AuthorizationException('You are not authorized to update this submission.');
        }

        $validator = Validator::make($data, [
            'file_url' => 'sometimes|string|max:255',
            'grade' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->repo->update($submission, $data);
    }
}
