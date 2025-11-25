<?php

namespace App\Http\Controllers\Api\BlogController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10); // default 10 per page
        $blogs = Blog::paginate($limit);
        return response()->json($blogs);
    }

    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        return response()->json($blog);
    }
}