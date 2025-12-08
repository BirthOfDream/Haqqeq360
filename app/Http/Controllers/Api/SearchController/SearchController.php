<?php

namespace App\Http\Controllers\Api\SearchController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bootcamp;
use App\Models\Course;
use App\Models\Workshop;
use App\Models\Assignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Search across multiple models for authenticated user
     * GET /api/search
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user = Auth::user();
            $query = $request->input('q', '');
            $type = $request->input('type', 'all'); // all, bootcamps, courses, workshops, assignments
            $level = $request->input('level'); // beginner, intermediate, advanced
            $mode = $request->input('mode'); // online, hybrid, offline
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $perPage = $request->input('per_page', 15);

            if (empty($query) && !$level && !$mode && !$minPrice && !$maxPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide at least one search parameter'
                ], 400);
            }

            $results = [];

            // Search Bootcamps
            if ($type === 'all' || $type === 'bootcamps') {
                $bootcamps = Bootcamp::query()
                    ->where(function ($q) use ($query) {
                        if (!empty($query)) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('description', 'LIKE', "%{$query}%");
                        }
                    })
                    ->when($level, fn($q) => $q->where('level', $level))
                    ->when($mode, fn($q) => $q->where('mode', $mode))
                    ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
                    ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
                    ->with('instructor:id,name,first_name,second_name,email')
                    ->get()
                    ->map(function ($bootcamp) {
                        return [
                            'id' => $bootcamp->id,
                            'type' => 'bootcamp',
                            'title' => $bootcamp->title,
                            'slug' => $bootcamp->slug,
                            'description' => $bootcamp->description,
                            'price' => $bootcamp->price,
                            'discounted_price' => $bootcamp->discounted_price,
                            'level' => $bootcamp->level,
                            'mode' => $bootcamp->mode,
                            'duration_weeks' => $bootcamp->duration_weeks,
                            'seats' => $bootcamp->seats,
                            'certificate' => $bootcamp->certificate,
                            'special' => $bootcamp->special,
                            'start_date' => $bootcamp->start_date,
                            'cover_image' => $bootcamp->cover_image,
                            'instructor' => $bootcamp->instructor,
                            'created_at' => $bootcamp->created_at,
                        ];
                    });

                $results['bootcamps'] = $bootcamps;
            }

            // Search Courses
            if ($type === 'all' || $type === 'courses') {
                $courses = Course::query()
                    ->where('status', 'published')
                    ->where(function ($q) use ($query) {
                        if (!empty($query)) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('description', 'LIKE', "%{$query}%");
                        }
                    })
                    ->when($level, fn($q) => $q->where('level', $level))
                    ->when($mode, fn($q) => $q->where('mode', $mode))
                    ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
                    ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
                    ->with('instructor:id,name,first_name,second_name,email')
                    ->get()
                    ->map(function ($course) {
                        return [
                            'id' => $course->id,
                            'type' => 'course',
                            'title' => $course->title,
                            'slug' => $course->slug,
                            'description' => $course->description,
                            'price' => $course->price,
                            'discounted_price' => $course->discounted_price,
                            'level' => $course->level,
                            'mode' => $course->mode,
                            'duration_weeks' => $course->duration_weeks,
                            'seats' => $course->seats,
                            'cover_image' => $course->cover_image,
                            'status' => $course->status,
                            'instructor' => $course->instructor,
                            'created_at' => $course->created_at,
                        ];
                    });

                $results['courses'] = $courses;
            }

            // Search Workshops
            if ($type === 'all' || $type === 'workshops') {
                $workshops = Workshop::query()
                    ->where('status', 'published')
                    ->where(function ($q) use ($query) {
                        if (!empty($query)) {
                            $q->where('title', 'LIKE', "%{$query}%")
                              ->orWhere('description', 'LIKE', "%{$query}%");
                        }
                    })
                    ->when($level, fn($q) => $q->where('level', $level))
                    ->when($mode, fn($q) => $q->where('mode', $mode))
                    ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
                    ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
                    ->with('user:id,name,first_name,second_name,email')
                    ->get()
                    ->map(function ($workshop) {
                        return [
                            'id' => $workshop->id,
                            'type' => 'workshop',
                            'title' => $workshop->title,
                            'slug' => $workshop->slug,
                            'description' => $workshop->description,
                            'price' => $workshop->price,
                            'discounted_price' => $workshop->discounted_price,
                            'level' => $workshop->level,
                            'mode' => $workshop->mode,
                            'duration_hours' => $workshop->duration_hours,
                            'seats' => $workshop->seats,
                            'cover_image' => $workshop->cover_image,
                            'status' => $workshop->status,
                            'instructor' => $workshop->user,
                            'created_at' => $workshop->created_at,
                        ];
                    });

                $results['workshops'] = $workshops;
            }

            // Search Assignments - SIMPLIFIED VERSION
            // Only search by title/description, don't filter by enrollment
            if ($type === 'all' || $type === 'assignments') {
                try {
                    $assignments = Assignment::query()
                        ->where(function ($q) use ($query) {
                            if (!empty($query)) {
                                $q->where('title', 'LIKE', "%{$query}%")
                                  ->orWhere('description', 'LIKE', "%{$query}%");
                            }
                        })
                        ->with('lesson:id,title')
                        ->get()
                        ->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'type' => 'assignment',
                                'title' => $assignment->title,
                                'description' => $assignment->description,
                                'due_date' => $assignment->due_date,
                                'lesson' => $assignment->lesson ? [
                                    'id' => $assignment->lesson->id,
                                    'title' => $assignment->lesson->title,
                                ] : null,
                                'created_at' => $assignment->created_at,
                            ];
                        });

                    $results['assignments'] = $assignments;
                } catch (\Exception $e) {
                    Log::warning('Assignment search failed: ' . $e->getMessage());
                    $results['assignments'] = collect([]);
                }
            }

            // Calculate total results
            $totalResults = collect($results)->sum(fn($items) => $items->count());

            // Flatten all results if searching across all types
            if ($type === 'all') {
                $allResults = collect($results)->flatten(1)->sortByDesc('created_at')->values();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Search completed successfully',
                    'data' => [
                        'query' => $query,
                        'filters' => [
                            'type' => $type,
                            'level' => $level,
                            'mode' => $mode,
                            'min_price' => $minPrice,
                            'max_price' => $maxPrice,
                        ],
                        'total_results' => $totalResults,
                        'results' => $allResults,
                        'breakdown' => [
                            'bootcamps' => $results['bootcamps']->count(),
                            'courses' => $results['courses']->count(),
                            'workshops' => $results['workshops']->count(),
                            'assignments' => $results['assignments']->count(),
                        ]
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => [
                    'query' => $query,
                    'filters' => [
                        'type' => $type,
                        'level' => $level,
                        'mode' => $mode,
                        'min_price' => $minPrice,
                        'max_price' => $maxPrice,
                    ],
                    'total_results' => $totalResults,
                    'results' => $results[$type],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}