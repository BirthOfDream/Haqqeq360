<?php

namespace App\Filament\Resources\CourseResource\Widgets;

use App\Models\Course;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CourseStatsOverview extends BaseWidget
{
    public ?Course $record = null;

    protected function getStats(): array
    {
        $activeEnrollments = $this->record->enrollments()->where('status', 'active')->count();
        $completedEnrollments = $this->record->enrollments()->where('status', 'completed')->count();
        $totalAssignments = $this->record->assignments()->count();

        return [
            Stat::make('Active Enrollments', $activeEnrollments)
                ->description('Currently enrolled students')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('Completed Enrollments', $completedEnrollments)
                ->description('Students who finished')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
            
            Stat::make('Total Assignments', $totalAssignments)
                ->description('Course assignments')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}