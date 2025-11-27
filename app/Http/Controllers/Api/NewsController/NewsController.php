<?php

namespace App\Http\Controllers\Api\NewsController;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a paginated list of news.
     * Frontend can control: ?page=1&limit=20
     */
    public function index(Request $request)
    {
        // Limit per page (default 10)
        $limit = $request->input('limit', 10);

        // Optional: only published news for frontend
        $news = News::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Display a single news item by ID.
     */
    public function show($id)
    {
        $news = News::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * Store a new news entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'status'     => 'required|in:draft,published',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        }

        $news = News::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'News created successfully.',
            'data' => $news
        ], 201);
    }
}
