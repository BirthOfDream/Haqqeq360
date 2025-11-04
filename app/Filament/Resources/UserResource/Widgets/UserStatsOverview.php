<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{
    public ?User $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Enrollments', $this->record->enrollments()->count())
                ->description('All time enrollments')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('success'),
            
            Stat::make('Courses Created', $this->record->courses()->count())
                ->description('As instructor')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->visible(fn () => in_array($this->record->role, ['instructor', 'admin'])),
            
            Stat::make('Bootcamps Created', $this->record->bootcamps()->count())
                ->description('As instructor')
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger')
                ->visible(fn () => in_array($this->record->role, ['instructor', 'admin'])),
        ];
    }
}