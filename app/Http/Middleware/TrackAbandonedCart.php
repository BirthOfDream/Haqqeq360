<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AbandonedCartService;
use Symfony\Component\HttpFoundation\Response;

class TrackAbandonedCart
{
    public function __construct(private AbandonedCartService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // تتبع السلة فقط للمستخدمين المسجلين
        if (!auth()->check()) {
            return $response;
        }

        // تحديد الخطوات التي نريد تتبعها
        $trackableSteps = [
            '/cart' => 'cart',
            '/checkout' => 'checkout',
            '/checkout/review' => 'review',
            '/checkout/payment' => 'payment',
        ];

        $currentPath = $request->path();
        
        foreach ($trackableSteps as $path => $step) {
            if (str_starts_with('/' . $currentPath, $path)) {
                // الحصول على بيانات السلة من الجلسة أو الطلب
                $cartData = session('cart', []);
                
                if (!empty($cartData)) {
                    foreach ($cartData as $item) {
                        $this->service->trackAbandonment(
                            userId: auth()->id(),
                            productId: $item['product_id'] ?? null,
                            lastStep: $step,
                            cartData: $cartData
                        );
                    }
                }
                break;
            }
        }

        return $response;
    }
}