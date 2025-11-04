<?php

namespace App\Actions\Bootcamps;

use App\Repositories\Interfaces\BootcampRepositoryInterface;

class IndexBootcampAction
{
    protected $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute()
    {
        return $this->repo->all();
    }
}
