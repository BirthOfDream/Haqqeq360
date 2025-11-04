<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Enrollments')
                ->badge(fn () => $this->getModel()::count()),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => $this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => $this->getModel()::where('status', 'active')->count())
                ->badgeColor('success'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => $this->getModel()::where('status', 'completed')->count())
                ->badgeColor('primary'),

            'low_progress' => Tab::make('Low Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'active')
                    ->where('progress', '<', 25))
                ->badge(fn () => $this->getModel()::where('status', 'active')
                    ->where('progress', '<', 25)->count())
                ->badgeColor('danger'),

            'high_progress' => Tab::make('High Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', 'active')
                    ->where('progress', '>=', 75))
                ->badge(fn () => $this->getModel()::where('status', 'active')
                    ->where('progress', '>=', 75)->count())
                ->badgeColor('success'),

            'bootcamp' => Tab::make('Bootcamp Enrollments')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollable_type', 'App\\Models\\Bootcamp'))
                ->badge(fn () => $this->getModel()::where('enrollable_type', 'App\\Models\\Bootcamp')->count()),

            'recent' => Tab::make('Recent (7 days)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getModel()::where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('info'),
        ];
    }
}
