<?php

namespace App\Http\Controllers\Api\CategoryController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\App;

class CategoryController extends Controller
{
    protected $categoryRepo;

    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $filters = $request->only(['is_active']);

        $categories = $this->categoryRepo->all($filters, $limit, $page);

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function show($idOrSlug)
    {
        $category = $this->categoryRepo->findById($idOrSlug) 
                    ?? $this->categoryRepo->findBySlug($idOrSlug);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }
}
