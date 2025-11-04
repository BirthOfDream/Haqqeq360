<?php

namespace App\Actions\Submissions;

use App\Repositories\SubmissionRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class CreateSubmissionAction
{
    protected $repo;

    public function __construct(SubmissionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data)
    {
        $validator = Validator::make($data, [
            'assignment_id' => 'required|exists:assignments,id',
            'file_url' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data['user_id'] = Auth::id();
        $data['submitted_at'] = now();

        return $this->repo->create($data);
    }
}
