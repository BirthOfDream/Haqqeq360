<?php

namespace App\Http\Controllers\Api\ProgramCategoryController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProgramCategory;


class ProgramCategoryController extends Controller
{
     public function index(Request $request)
    {
        // Read per page from query. Default 10. Clamp to 1..100 for safety.
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        // Use paginate. Page is read from query string key page.
        $page = (int) $request->query('page', 1);
        $data = ProgramCategory::orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        // Return a clean JSON shape.
        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
            ],
            'links' => [
                'next' => $data->nextPageUrl(),
                'prev' => $data->previousPageUrl(),
            ],
        ]);
    }

    public function show($id)
    {
        $category = ProgramCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Program Category not found'], 404);
        }

        return response()->json($category);
    }

    

}
