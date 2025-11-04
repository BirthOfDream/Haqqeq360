<?php

namespace App\Actions\Programs;

use App\Repositories\Interfaces\ProgramRepositoryInterface;
use Illuminate\Support\Facades\App;

class ListProgramsAction
{
    public static function execute(array $filters = [], int $limit = 10, int $page = 1)
    {
        $repo = App::make(ProgramRepositoryInterface::class);
        $programs = $repo->all($filters, $limit, $page);

        return response()->json([
            'status' => 'success',
            'data' => $programs
        ]);
    }
}
