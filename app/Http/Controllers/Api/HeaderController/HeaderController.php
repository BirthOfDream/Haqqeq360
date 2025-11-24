<?php

namespace App\Http\Controllers\Api\HeaderController;

use App\Http\Controllers\Controller;
use App\Repositories\HeaderRepository\HeaderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Header;

class HeaderController extends Controller
{
    protected $repo;

    public function __construct(HeaderRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        try {
            $headers = $this->repo->getAll();
            return response()->json(['success' => true, 'data' => $headers]);
        } catch (Exception $e) {
            Log::error('Fetch headers failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }  

    public function show($idOrSlug)
{
    try {
        $header = $this->repo->find($idOrSlug);
        return response()->json(['success' => true, 'data' => $header]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => 'Header not found'], 404);
    }
}


}