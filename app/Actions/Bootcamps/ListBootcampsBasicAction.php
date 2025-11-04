<?php

namespace App\Actions\Bootcamps;

use App\Repositories\Interfaces\BootcampRepositoryInterface;

class ListBootcampsBasicAction
{
    protected BootcampRepositoryInterface $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $limit = 10)
    {
        $bootcamps = $this->repo->allBasic($limit);

        return response()->json([
            'success' => true,
            'data' => $bootcamps
        ]);
    }
}
