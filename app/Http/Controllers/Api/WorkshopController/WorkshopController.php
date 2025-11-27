<?php

namespace App\Http\Controllers\Api\WorkshopController;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    /**
     * GET /api/workshops
     * Fetch all workshops with optional filters + pagination
     * Params example:
     *  ?page=1&limit=20&level=beginner&mode=online&status=published
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // default pagination 10

        $query = Workshop::query()->with('instructor');

        // Optional Filters
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('mode')) {
            $query->where('mode', $request->mode);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Show only published for public API by default
            $query->where('status', 'published');
        }

        $workshops = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data'    => $workshops,
        ]);
    }


    /**
     * GET /api/workshops/{id}
     * Show a single workshop with instructor relationship
     */
    public function show($id)
    {
        $workshop = Workshop::with('instructor')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $workshop
        ]);
    }
}
