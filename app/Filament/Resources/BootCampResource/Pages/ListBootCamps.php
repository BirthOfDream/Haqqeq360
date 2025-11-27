<?php
// File: app/Filament/Resources/BootcampResource/Pages/ListBootcamps.php

namespace App\Filament\Resources\BootCampResource\Pages;

use App\Filament\Resources\BootcampResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBootCamps extends ListRecords
{
    protected static string $resource = BootcampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Bootcamps')
                ->badge(fn () => $this->getModel()::count()),

            'upcoming' => Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('start_date', '>=', now()))
                ->badge(fn () => $this->getModel()::where('start_date', '>=', now())->count()),

            'ongoing' => Tab::make('Ongoing')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('start_date', '<=', now())
                    ->whereRaw('DATE_ADD(start_date, INTERVAL duration_weeks WEEK) >= ?', [now()]))
                ->badge(fn () => $this->getModel()::where('start_date', '<=', now())
                    ->whereRaw('DATE_ADD(start_date, INTERVAL duration_weeks WEEK) >= ?', [now()])
                    ->count()),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereRaw('DATE_ADD(start_date, INTERVAL duration_weeks WEEK) < ?', [now()]))
                ->badge(fn () => $this->getModel()::whereRaw('DATE_ADD(start_date, INTERVAL duration_weeks WEEK) < ?', [now()])
                    ->count()),

            'beginner' => Tab::make('Beginner')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 'beginner'))
                ->badge(fn () => $this->getModel()::where('level', 'beginner')->count()),

            'intermediate' => Tab::make('Intermediate')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 'intermediate'))
                ->badge(fn () => $this->getModel()::where('level', 'intermediate')->count()),

            'advanced' => Tab::make('Advanced')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('level', 'advanced'))
                ->badge(fn () => $this->getModel()::where('level', 'advanced')->count()),

            'with_certificate' => Tab::make('With Certificate')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('certificate', true))
                ->badge(fn () => $this->getModel()::where('certificate', true)->count()),
        ];
    }
}