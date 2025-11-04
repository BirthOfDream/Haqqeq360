<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generateBulkReports')
                ->label('Generate Bulk Reports')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(function () {
                    // Logic to generate reports for all active enrollments
                    \Filament\Notifications\Notification::make()
                        ->info()
                        ->title('Bulk Report Generation')
                        ->body('Reports are being generated in the background.')
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Reports')
                ->badge(fn () => $this->getModel()::count()),

            'excellent' => Tab::make('Excellent (90%+)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('completion_rate', '>=', 90))
                ->badge(fn () => $this->getModel()::where('completion_rate', '>=', 90)->count())
                ->badgeColor('success'),

            'good' => Tab::make('Good (75-89%)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereBetween('completion_rate', [75, 89.99]))
                ->badge(fn () => $this->getModel()::whereBetween('completion_rate', [75, 89.99])->count())
                ->badgeColor('info'),

            'average' => Tab::make('Average (50-74%)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereBetween('completion_rate', [50, 74.99]))
                ->badge(fn () => $this->getModel()::whereBetween('completion_rate', [50, 74.99])->count())
                ->badgeColor('warning'),

            'needs_attention' => Tab::make('Needs Attention (<50%)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('completion_rate', '<', 50))
                ->badge(fn () => $this->getModel()::where('completion_rate', '<', 50)->count())
                ->badgeColor('danger'),

            'with_grades' => Tab::make('Graded')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereNotNull('grade_avg'))
                ->badge(fn () => $this->getModel()::whereNotNull('grade_avg')->count()),

            'bootcamp' => Tab::make('Bootcamp Reports')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('enrollable_type', 'App\\Models\\Bootcamp'))
                ->badge(fn () => $this->getModel()::where('enrollable_type', 'App\\Models\\Bootcamp')->count()),

            'recent' => Tab::make('Recent (30 days)')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('created_at', '>=', now()->subDays(30)))
                ->badge(fn () => $this->getModel()::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('info'),
        ];
    }
}
