<?php

namespace App\Repositories\Program;

use App\Models\Program;
use App\Repositories\Interfaces\ProgramRepositoryInterface;

class ProgramRepository implements ProgramRepositoryInterface
{
    public function all(array $filters = [], int $limit = 10, int $page = 1)
    {
        $query = Program::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_published'])) {
            $query->where('is_published', $filters['is_published']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById(string $id): ?Program
    {
        return Program::find($id);
    }

    public function findBySlug(string $slug): ?Program
    {
        return Program::where('slug', $slug)->first();
    }
}
