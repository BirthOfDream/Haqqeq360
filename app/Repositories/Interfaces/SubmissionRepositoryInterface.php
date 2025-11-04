<?php

namespace App\Repositories;

use App\Models\Submission;

interface SubmissionRepositoryInterface
{
    public function create(array $data): Submission;
    public function update(Submission $submission, array $data): Submission;
    public function delete(Submission $submission): bool;
    public function findById(int $id): ?Submission;
}
