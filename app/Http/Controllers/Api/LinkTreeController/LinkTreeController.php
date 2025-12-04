<?php

namespace App\Http\Controllers\Api\LinkTreeController;

use App\Http\Controllers\Controller;
use App\Models\LinkTreeSetting;
use App\Models\LinkTreeLink;
use App\Models\LinkTreeClick;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LinkTreeController extends Controller
{
    /**
     * Get link tree page data
     * GET /api/link-tree/{slug?}
     */
    public function show(string $slug = 'links'): JsonResponse
    {
        try {
            $settings = LinkTreeSetting::where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'صفحة الروابط غير موجودة'
                ], 404);
            }

            $links = LinkTreeLink::active()
                ->ordered()
                ->get(['id', 'name', 'url', 'icon', 'order']);

            return response()->json([
                'success' => true,
                'data' => [
                    'settings' => [
                        'background_color' => $settings->background_color,
                        'button_color' => $settings->button_color,
                        'text_color' => $settings->text_color,
                        'font_family' => $settings->font_family,
                        'page_title' => $settings->page_title,
                        'page_description' => $settings->page_description,
                    ],
                    'links' => $links,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب البيانات'
            ], 500);
        }
    }

    /**
     * Track link click
     * POST /api/link-tree/track/{linkId}
     */
    public function trackClick(Request $request, int $linkId): JsonResponse
    {
        try {
            $link = LinkTreeLink::active()->findOrFail($linkId);

            // Record click
            LinkTreeClick::create([
                'link_id' => $link->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'clicked_at' => now(),
            ]);

            // Increment counter
            $link->incrementClicks();

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $link->url,
                    'total_clicks' => $link->clicks,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'الرابط غير موجود'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل النقرة'
            ], 500);
        }
    }

    /**
     * Get analytics for all links
     * GET /api/link-tree/analytics
     */
    public function analytics(): JsonResponse
    {
        try {
            $links = LinkTreeLink::withCount('clickRecords')
                ->orderByDesc('clicks')
                ->get(['id', 'name', 'url', 'clicks', 'created_at']);

            $totalClicks = $links->sum('clicks');

            $recentClicks = LinkTreeClick::with('link:id,name')
                ->latest('clicked_at')
                ->take(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_clicks' => $totalClicks,
                    'links' => $links,
                    'recent_clicks' => $recentClicks,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات'
            ], 500);
        }
    }
}

/**
 * API Routes (add to routes/api.php):
 * 
 * Route::prefix('link-tree')->group(function () {
 *     Route::get('/{slug?}', [LinkTreeController::class, 'show']);
 *     Route::post('/track/{linkId}', [LinkTreeController::class, 'trackClick']);
 *     Route::get('/analytics', [LinkTreeController::class, 'analytics'])->middleware('auth:sanctum');
 * });
 */