<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'published']))
                ->visible(fn () => $this->record->status === 'draft'),
            
            Actions\Action::make('unpublish')
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'draft']))
                ->visible(fn () => $this->record->status === 'published'),
            
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Course Information')
                    ->schema([
                        Components\ImageEntry::make('cover_image')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                        
                        Components\TextEntry::make('title')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Components\TextEntry::make('slug')
                            ->copyable(),
                        
                        Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->prose(),
                        
                        Components\TextEntry::make('instructor.name')
                            ->badge()
                            ->color('info'),
                        
                        Components\TextEntry::make('duration_weeks')
                            ->suffix(' weeks')
                            ->badge(),
                    ])
                    ->columns(2),
                
                Components\Section::make('Course Details')
                    ->schema([
                        Components\TextEntry::make('level')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'beginner' => 'success',
                                'intermediate' => 'warning',
                                'advanced' => 'danger',
                            }),
                        
                        Components\TextEntry::make('mode')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'online' => 'info',
                                'hybrid' => 'warning',
                                'offline' => 'secondary',
                            }),
                        
                        Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'secondary',
                                'published' => 'success',
                            }),
                    ])
                    ->columns(3),
                
                Components\Section::make('Statistics')
                    ->schema([
                        Components\TextEntry::make('enrollments_count')
                            ->label('Total Enrollments')
                            ->state(fn ($record) => $record->enrollments()->count())
                            ->badge()
                            ->color('success'),
                        
                        Components\TextEntry::make('assignments_count')
                            ->label('Total Assignments')
                            ->state(fn ($record) => $record->assignments()->count())
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),
                
                Components\Section::make('Timestamps')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->dateTime(),
                        
                        Components\TextEntry::make('updated_at')
                            ->dateTime(),
                        
                        Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn ($record) => $record->trashed()),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            CourseResource\Widgets\CourseStatsOverview::class,
        ];
    }
}