<?php


namespace App\Http\Controllers\Api\PlanController;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::with('planable')
            ->active()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price' => $plan->price,
                    'type' => $plan->plan_type,
                    'planable' => [
                        'id' => $plan->planable->id,
                        'title' => $plan->planable->title,
                        'description' => $plan->planable->description,
                    ],
                    'features' => $plan->features,
                    'is_active' => $plan->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->load('planable');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'type' => $plan->plan_type,
                'planable' => $plan->planable,
                'features' => $plan->features,
                'is_active' => $plan->is_active,
            ],
        ]);
    }

    public function byType(string $type): JsonResponse
    {
        $modelMap = [
            'workshops' => 'App\Models\Workshop',
            'bootcamps' => 'App\Models\Bootcamp',
            'programs' => 'App\Models\Program',
            'courses' => 'App\Models\Course',
        ];

        if (!isset($modelMap[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type',
            ], 400);
        }

        $plans = Plan::where('planable_type', $modelMap[$type])
            ->with('planable')
            ->active()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }
}