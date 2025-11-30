<?php 

namespace App\Http\Controllers\Api\WhyChooseFeatureController;
use App\Http\Controllers\Controller;
use App\Repositories\WhyChooseFeatureRepository\WhyChooseFeatureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhyChooseFeatureController extends Controller
{
    protected $whyChooseFeatureRepository;

    /**
     * Create a new controller instance.
     *
     * @param WhyChooseFeatureRepository $whyChooseFeatureRepository
     */
    public function __construct(WhyChooseFeatureRepository $whyChooseFeatureRepository)
    {
        $this->whyChooseFeatureRepository = $whyChooseFeatureRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $perPage = max(1, min(100, (int) $perPage));

            $features = $this->whyChooseFeatureRepository->getActivePaginated($perPage);

            return response()->json([
                'data' => $features->items(),
                'current_page' => $features->currentPage(),
                'last_page' => $features->lastPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching features.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $feature = $this->whyChooseFeatureRepository->findById($id);

            if (!$feature) {
                return response()->json([
                    'message' => 'Feature not found.',
                ], 404);
            }

            return response()->json([
                'data' => $feature,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the feature.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}