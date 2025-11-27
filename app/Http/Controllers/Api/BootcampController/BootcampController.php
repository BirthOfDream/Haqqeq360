<?php

namespace App\Http\Controllers\Api\BootcampController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Bootcamps\{
    ListBootcampsBasicAction,
    ShowBootcampAction,
    SearchBootcampAction,
    FilterBootcampAction
};

class BootcampController extends Controller
{
    public function index(Request $request, ListBootcampsBasicAction $action)
    {
        $limit = $request->input('limit', 10);
        $bootcamps = $action->execute($limit);
        
        return response()->json([
            'success' => true,
            'data' => $bootcamps
        ]);
    }

    public function show(Request $request, int $id, ShowBootcampAction $action)
    {
        $bootcamp = $action->execute($id);
        
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

    public function search(Request $request, SearchBootcampAction $action)
    {
        $searchTerm = $request->input('search', '');
        $limit = $request->input('limit', 10);
        
        $bootcamps = $action->execute($searchTerm, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $bootcamps
        ]);
    }

    public function filter(Request $request, FilterBootcampAction $action)
    {
        $filters = $request->only(['level', 'mode', 'certificate', 'has_seats']);
        $limit = $request->input('limit', 10);
        
        $bootcamps = $action->execute($filters, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $bootcamps
        ]);
    }
}
