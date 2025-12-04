<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge($this->getModel()::where('is_active', true)->count()),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
            'workshops' => Tab::make('Workshops')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('planable_type', 'App\Models\Workshop')),
            'bootcamps' => Tab::make('Bootcamps')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('planable_type', 'App\Models\Bootcamp')),
            'programs' => Tab::make('Programs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('planable_type', 'App\Models\Program')),
            'courses' => Tab::make('Courses')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('planable_type', 'App\Models\Course')),
        ];
    }
}
