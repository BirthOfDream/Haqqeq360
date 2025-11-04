<?php

namespace App\Http\Controllers\Api\ProgramController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\ProgramRepositoryInterface;
use App\Actions\Programs\ListProgramsAction;
use App\Actions\Programs\ShowProgramAction;

class ProgramController extends Controller
{
    protected $programRepo;

    public function __construct(ProgramRepositoryInterface $programRepo)
    {
        $this->programRepo = $programRepo;
    }

    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $filters = $request->only(['type', 'is_published', 'category_id']);

        return ListProgramsAction::execute($filters, $limit, $page);
    }

    public function show(Request $request, $idOrSlug)
    {
        return ShowProgramAction::execute($idOrSlug);
    }
}
