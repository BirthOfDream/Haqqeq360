<?php

namespace App\Http\Controllers\Api\DigitalProductController;

use App\Http\Controllers\Controller;
use App\Models\DigitalProduct;
use Illuminate\Http\Request;

class DigitalProductController extends Controller
{
    /**
     * GET /api/digital-products
     * Pagination + limit + optional status filter
     * Example: /api/digital-products?page=1&limit=15&status=published&type=e-book
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // default 10 per page

        $query = DigitalProduct::query();

        // Optional Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'published'); // default public view
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }


    /**
     * GET /api/digital-products/{id}
     * Return single product
     */
    public function show($id)
    {
        $product = DigitalProduct::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $product
        ]);
    }

        public function search($keyword, Request $request)
    {
        $limit = $request->input('limit', 10);

        $results = DigitalProduct::where('title', 'LIKE', "%{$keyword}%")
            ->orWhere('description', 'LIKE', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }

}
