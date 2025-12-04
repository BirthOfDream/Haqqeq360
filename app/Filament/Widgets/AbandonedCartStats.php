<?php

namespace App\Filament\Resources\AbandonedCartResource\Widgets;

use App\Models\AbandonedCart;
use App\Services\AbandonedCartService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbandonedCartStats extends BaseWidget
{
    protected function getStats(): array
    {
        $service = app(AbandonedCartService::class);
        $conversionData = $service->getConversionRate();
        
        $totalAbandoned = AbandonedCart::where('status', 'abandoned')->count();
        $totalReminded = AbandonedCart::where('status', 'reminded')->count();
        
        return [
            Stat::make('السلات المتروكة', $totalAbandoned)
                ->description('إجمالي السلات غير المكتملة')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('تم التذكير', $totalReminded)
                ->description('السلات التي تم إرسال تذكير لها')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('warning'),
            
            Stat::make('نسبة التحويل', $conversionData['rate'] . '%')
                ->description("تم تحويل {$conversionData['converted']} من {$conversionData['total']}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([3, 5, 7, 4, 6, 8, 10, 9]),
        ];
    }
}