<?php

// app/Filament/Widgets/UsersStatsWidget.php
namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Resources\UserResource;
use Illuminate\Support\Facades\Log;
class UsersStatsWidget extends BaseWidget
{
    protected function getStats(): array
{
    try {
        $userCount = User::count();
    } catch (\Exception $e) {
        \Log::error('Failed to fetch user count: ' . $e->getMessage());
        $userCount = 0;
    }

    return [
        Stat::make('Total Users', $userCount)
            ->description('All registered users')
            ->descriptionIcon('heroicon-m-users')
            ->color('success')
            ->url('/admin/users')
            ->extraAttributes([
                'class' => 'cursor-pointer',
            ]),
    ];
}
}