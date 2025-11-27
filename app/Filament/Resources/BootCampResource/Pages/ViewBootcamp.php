<?php
namespace App\Filament\Resources\BootCampResource\Pages;

use App\Filament\Resources\BootCampResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewBootCamp extends ViewRecord
{
    protected static string $resource = BootCampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Bootcamp Overview')
                    ->schema([
                        Components\ImageEntry::make('cover_image')
                            ->label('Cover Image')
                            ->columnSpanFull()
                            ->height(300)
                            ->extraAttributes(['class' => 'rounded-lg']),

                        Components\TextEntry::make('title')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->prose(),
                    ])
                    ->columns(1),

                Components\Section::make('Details')
                    ->schema([
                        Components\TextEntry::make('instructor.name')
                            ->label('Instructor')
                            ->icon('heroicon-o-user'),

                        Components\TextEntry::make('instructor.email')
                            ->label('Instructor Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

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
                                'online' => 'primary',
                                'hybrid' => 'info',
                                'offline' => 'secondary',
                            }),

                        Components\TextEntry::make('duration_weeks')
                            ->label('Duration')
                            ->suffix(' weeks')
                            ->icon('heroicon-o-clock'),

                        Components\TextEntry::make('seats')
                            ->label('Available Seats')
                            ->icon('heroicon-o-users')
                            ->default('Unlimited'),

                        Components\TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('F d, Y')
                            ->icon('heroicon-o-calendar')
                            ->color(fn ($state) => $state && $state->isFuture() ? 'success' : 'gray'),

                        Components\IconEntry::make('certificate')
                            ->label('Certificate Offered')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-badge')
                            ->falseIcon('heroicon-o-x-mark'),

                        Components\TextEntry::make('enrollments_count')
                            ->label('Total Enrollments')
                            ->state(fn ($record) => $record->enrollments()->count())
                            ->icon('heroicon-o-academic-cap')
                            ->badge()
                            ->color('info'),

                        Components\TextEntry::make('available_seats')
                            ->label('Remaining Seats')
                            ->state(function ($record) {
                                if (!$record->seats) return 'Unlimited';
                                $enrolled = $record->enrollments()->count();
                                $remaining = $record->seats - $enrolled;
                                return $remaining > 0 ? $remaining : 'Full';
                            })
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                $state === 'Unlimited' => 'success',
                                $state === 'Full' => 'danger',
                                (int)$state <= 5 => 'warning',
                                default => 'success',
                            }),
                    ])
                    ->columns(2),

                Components\Section::make('Timeline')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('F d, Y H:i'),

                        Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F d, Y H:i'),

                        Components\TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime('F d, Y H:i')
                            ->visible(fn ($record) => $record->trashed())
                            ->color('danger'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widgets here if needed
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // You can add widgets here if needed
        ];
    }
}