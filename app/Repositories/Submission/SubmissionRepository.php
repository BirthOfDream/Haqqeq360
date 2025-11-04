<?php

namespace App\Repositories;

use App\Models\Submission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SubmissionRepository implements SubmissionRepositoryInterface
{
    public function create(array $data): Submission
    {
        return Submission::create($data);
    }

    public function update(Submission $submission, array $data): Submission
    {
        $submission->update($data);
        return $submission;
    }

    public function delete(Submission $submission): bool
    {
        return $submission->delete();
    }

    public function findById(int $id): ?Submission
    {
        return Submission::find($id);
    }
}
