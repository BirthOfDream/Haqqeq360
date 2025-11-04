<?php

namespace App\Actions\Programs;

use App\Repositories\Interfaces\ProgramRepositoryInterface;
use Illuminate\Support\Facades\App;

class ShowProgramAction
{
    public static function execute(string $idOrSlug)
    {
        $repo = App::make(ProgramRepositoryInterface::class);
        $program = $repo->findById($idOrSlug) ?? $repo->findBySlug($idOrSlug);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Program not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $program
        ]);
    }
}
