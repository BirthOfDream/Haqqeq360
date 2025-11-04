<?php

namespace App\Actions\Bootcamps;

use App\Repositories\Interfaces\BootcampRepositoryInterface;

class ShowBootcampAction
{
    protected BootcampRepositoryInterface $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $id)
    {
        $bootcamp = $this->repo->find($id);

        if (!$bootcamp) {
            return response()->json([
                'success' => false,
                'message' => 'Bootcamp not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bootcamp
        ]);
    }
}
