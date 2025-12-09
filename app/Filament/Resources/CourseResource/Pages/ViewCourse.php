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
                            ->columnSpanFull()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&color=7F9CF5&background=EBF4FF&size=512'),
                        
                        Components\TextEntry::make('title')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Components\TextEntry::make('slug')
                            ->copyable()
                            ->icon('heroicon-o-link'),
                        
                        Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->prose()
                            ->markdown(),
                        
                        Components\TextEntry::make('instructor.name')
                            ->label('Instructor')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-user'),
                        
                        Components\TextEntry::make('duration_weeks')
                            ->label('Duration')
                            ->suffix(' weeks')
                            ->badge()
                            ->icon('heroicon-o-clock')
                            ->color('warning'),
                    ])
                    ->columns(2),
                
                Components\Section::make('Course Details')
                    ->schema([
                        Components\TextEntry::make('level')
                            ->badge()
                            ->icon(fn (string $state): string => match ($state) {
                                'beginner' => 'heroicon-o-star',
                                'intermediate' => 'heroicon-o-fire',
                                'advanced' => 'heroicon-o-rocket-launch',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'beginner' => 'success',
                                'intermediate' => 'warning',
                                'advanced' => 'danger',
                            }),
                        
                        Components\TextEntry::make('mode')
                            ->badge()
                            ->icon(fn (string $state): string => match ($state) {
                                'online' => 'heroicon-o-globe-alt',
                                'hybrid' => 'heroicon-o-arrow-path',
                                'offline' => 'heroicon-o-building-office-2',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'online' => 'info',
                                'hybrid' => 'warning',
                                'offline' => 'secondary',
                            }),
                        
                        Components\TextEntry::make('status')
                            ->badge()
                            ->icon(fn (string $state): string => match ($state) {
                                'draft' => 'heroicon-o-pencil',
                                'published' => 'heroicon-o-check-circle',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'secondary',
                                'published' => 'success',
                            }),
                    ])
                    ->columns(3),
                
                Components\Section::make('Pricing & Enrollment')
                    ->schema([
                        Components\TextEntry::make('price')
                            ->money('USD')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('success'),
                        
                        Components\TextEntry::make('discounted_price')
                            ->money('USD')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('danger')
                            ->placeholder('No discount')
                            ->visible(fn ($record) => $record->discounted_price !== null),
                        
                        Components\TextEntry::make('seats')
                            ->label('Total Seats')
                            ->numeric()
                            ->badge()
                            ->color('gray'),
                        
                        Components\TextEntry::make('enrollments_count')
                            ->label('Enrolled Students')
                            ->state(fn ($record) => $record->enrollments()->count())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-users'),
                        
                        Components\TextEntry::make('available_seats')
                            ->label('Available Seats')
                            ->state(function ($record) {
                                $enrolled = $record->enrollments()->count();
                                return max(0, $record->seats - $enrolled);
                            })
                            ->badge()
                            ->color(function ($record) {
                                $enrolled = $record->enrollments()->count();
                                $available = max(0, $record->seats - $enrolled);
                                return match (true) {
                                    $available === 0 => 'danger',
                                    $available <= 5 => 'warning',
                                    default => 'success',
                                };
                            })
                            ->icon(function ($record) {
                                $enrolled = $record->enrollments()->count();
                                $available = max(0, $record->seats - $enrolled);
                                return $available === 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                            }),
                    ])
                    ->columns(3),
                
                Components\Section::make('Course Content')
                    ->schema([
                        Components\RepeatableEntry::make('units')
                            ->schema([
                                Components\Grid::make(3)
                                    ->schema([
                                        Components\TextEntry::make('title')
                                            ->size(Components\TextEntry\TextEntrySize::Large)
                                            ->weight('bold')
                                            ->color('primary')
                                            ->icon('heroicon-o-book-open'),
                                        
                                        Components\TextEntry::make('order')
                                            ->badge()
                                            ->color('gray')
                                            ->prefix('Order: '),
                                        
                                        Components\TextEntry::make('lessons_count')
                                            ->label('Lessons')
                                            ->state(fn ($record) => $record->lessons()->count())
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-o-academic-cap'),
                                    ]),
                                
                                Components\RepeatableEntry::make('lessons')
                                    ->schema([
                                        Components\Grid::make(2)
                                            ->schema([
                                                Components\TextEntry::make('title')
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-document-text'),
                                                
                                                Components\TextEntry::make('order')
                                                    ->badge()
                                                    ->color('gray')
                                                    ->size(Components\TextEntry\TextEntrySize::Small),
                                            ]),
                                        
                                        Components\TextEntry::make('content')
                                            ->html()
                                            ->columnSpanFull()
                                            ->visible(fn ($record) => !empty($record->content)),
                                        
                                        Components\Grid::make(3)
                                            ->schema([
                                                Components\TextEntry::make('video_url')
                                                    ->label('Video')
                                                    ->url(fn ($record) => $record->video_url)
                                                    ->openUrlInNewTab()
                                                    ->icon('heroicon-o-play-circle')
                                                    ->color('danger')
                                                    ->visible(fn ($record) => !empty($record->video_url))
                                                    ->formatStateUsing(fn () => 'Watch Video'),
                                                
                                                Components\TextEntry::make('resource_link')
                                                    ->label('Resource')
                                                    ->url(fn ($record) => $record->resource_link)
                                                    ->openUrlInNewTab()
                                                    ->icon('heroicon-o-link')
                                                    ->color('info')
                                                    ->visible(fn ($record) => !empty($record->resource_link))
                                                    ->formatStateUsing(fn () => 'View Resource'),
                                                
                                                Components\TextEntry::make('attachment_path')
                                                    ->label('Attachment')
                                                    ->icon('heroicon-o-paper-clip')
                                                    ->color('success')
                                                    ->visible(fn ($record) => !empty($record->attachment_path))
                                                    ->formatStateUsing(fn ($state) => basename($state)),
                                            ]),
                                        
                                        Components\TextEntry::make('published')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => $state ? 'Published' : 'Draft')
                                            ->color(fn ($state) => $state ? 'success' : 'warning')
                                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                                        
                                        Components\Section::make('Assignment')
                                            ->schema([
                                                Components\TextEntry::make('assignment.title')
                                                    ->label('Title')
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-clipboard-document-check')
                                                    ->color('primary'),
                                                
                                                Components\TextEntry::make('assignment.max_score')
                                                    ->label('Max Score')
                                                    ->badge()
                                                    ->suffix(' points')
                                                    ->color('info'),
                                                
                                                Components\TextEntry::make('assignment.description')
                                                    ->label('Description')
                                                    ->html()
                                                    ->columnSpanFull(),
                                                
                                                Components\TextEntry::make('assignment.due_date')
                                                    ->label('Due Date')
                                                    ->dateTime()
                                                    ->icon('heroicon-o-calendar')
                                                    ->color('warning'),
                                                
                                                Components\TextEntry::make('assignment.attachment_path')
                                                    ->label('Attachment')
                                                    ->icon('heroicon-o-paper-clip')
                                                    ->color('success')
                                                    ->visible(fn ($record) => !empty($record->assignment?->attachment_path))
                                                    ->formatStateUsing(fn ($state) => basename($state)),
                                                
                                                Components\TextEntry::make('assignment.published')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                                                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                                            ])
                                            ->columns(2)
                                            ->visible(fn ($record) => $record->assignment !== null)
                                            ->collapsed()
                                            ->collapsible(),
                                    ])
                                    ->contained(false)
                                    ->columnSpanFull(),
                            ])
                            ->contained(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record->units()->count() > 0)
                    ->collapsible()
                    ->collapsed(false),
                
                Components\Section::make('Statistics')
                    ->schema([
                        Components\TextEntry::make('total_units')
                            ->label('Total Units')
                            ->state(fn ($record) => $record->units()->count())
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-book-open'),
                        
                        Components\TextEntry::make('total_lessons')
                            ->label('Total Lessons')
                            ->state(fn ($record) => $record->units()->withCount('lessons')->get()->sum('lessons_count'))
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-academic-cap'),
                        
                        Components\TextEntry::make('total_assignments')
                            ->label('Total Assignments')
                            ->state(fn ($record) => $record->assignments()->count())
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-clipboard-document-check'),
                        
                        Components\TextEntry::make('completion_rate')
                            ->label('Avg Completion Rate')
                            ->state(function ($record) {
                                $enrollments = $record->enrollments;
                                if ($enrollments->isEmpty()) {
                                    return '0%';
                                }
                                $avgProgress = $enrollments->avg('progress') ?? 0;
                                return round($avgProgress) . '%';
                            })
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-chart-bar'),
                    ])
                    ->columns(4),
                
                Components\Section::make('Timestamps')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar'),
                        
                        Components\TextEntry::make('updated_at')
                            ->dateTime()
                            ->icon('heroicon-o-calendar')
                            ->since(),
                        
                        Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn ($record) => $record->trashed()),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Uncomment if you have this widget
            // CourseResource\Widgets\CourseStatsOverview::class,
        ];
    }
}