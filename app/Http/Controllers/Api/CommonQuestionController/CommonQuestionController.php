<?php

namespace App\Http\Controllers\Api\CommonQuestionController;

use App\Http\Controllers\Controller;
use App\Models\CommonQuestion;
use Illuminate\Http\Request;

class CommonQuestionController extends Controller
{
    /**
     * List questions with pagination
     * Example: /api/common-questions?page=1&limit=15
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // default 10 per page

        $questions = CommonQuestion::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data'    => $questions
        ]);
    }

    /**
     * Show single question by ID
     * GET /api/common-questions/{id}
     */
    public function show($id)
    {
        $question = CommonQuestion::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $question
        ]);
    }
}
