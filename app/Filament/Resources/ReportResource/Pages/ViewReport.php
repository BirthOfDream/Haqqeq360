<?php
namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('downloadPDF')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Logic to generate and download PDF
                    \Filament\Notifications\Notification::make()
                        ->info()
                        ->title('Downloading')
                        ->body('Your report PDF is being generated.')
                        ->send();
                }),
            Actions\Action::make('emailReport')
                ->label('Email Report')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    // Logic to email report to student
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Email Sent')
                        ->body('The report has been emailed to the student.')
                        ->send();
                }),
            Actions\Action::make('printReport')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    // Logic to open print dialog
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Student Information')
                    ->schema([
                        Components\ImageEntry::make('user.avatar')
                            ->label('Avatar')
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name)),

                        Components\TextEntry::make('user.name')
                            ->label('Student Name')
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        Components\TextEntry::make('enrollable_type')
                            ->label('Program Type')
                            ->formatStateUsing(fn (string $state): string => class_basename($state))
                            ->badge()
                            ->color('info'),

                        Components\TextEntry::make('enrollable_id')
                            ->label('Program')
                            ->getStateUsing(function ($record) {
                                $model = app($record->enrollable_type);
                                $program = $model->find($record->enrollable_id);
                                return $program?->title ?? 'N/A';
                            })
                            ->size(Components\TextEntry\TextEntrySize::Large),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Components\Section::make('Performance Summary')
                    ->schema([
                        Components\TextEntry::make('completion_rate')
                            ->label('Completion Rate')
                            ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
                            ->badge()
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->color(fn (string $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 75 => 'info',
                                $state >= 50 => 'warning',
                                default => 'danger',
                            }),

                        Components\TextEntry::make('grade_avg')
                            ->label('Average Grade')
                            ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2) . '%' : 'Not Graded')
                            ->badge()
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->color(fn ($state): string => match (true) {
                                !$state => 'gray',
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                default => 'danger',
                            }),

                        Components\TextEntry::make('status')
                            ->label('Overall Status')
                            ->getStateUsing(function ($record) {
                                if ($record->completion_rate >= 90) return 'Excellent Performance';
                                if ($record->completion_rate >= 75) return 'Good Performance';
                                if ($record->completion_rate >= 50) return 'Average Performance';
                                return 'Needs Improvement';
                            })
                            ->badge()
                            ->size(Components\TextEntry\TextEntrySize::Large)
                            ->color(fn (string $state): string => match ($state) {
                                'Excellent Performance' => 'success',
                                'Good Performance' => 'info',
                                'Average Performance' => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(3),

                Components\Section::make('Detailed Feedback')
                    ->schema([
                        Components\TextEntry::make('feedback_summary')
                            ->label('')
                            ->columnSpanFull()
                            ->prose()
                            ->placeholder('No feedback has been provided yet.'),
                    ])
                    ->collapsible(),

                Components\Section::make('Report Metadata')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->label('Generated On')
                            ->dateTime('F d, Y \a\t H:i'),

                        Components\TextEntry::make('updated_at')
                            ->label('Last Modified')
                            ->dateTime('F d, Y \a\t H:i'),

                        Components\TextEntry::make('days_since_generation')
                            ->label('Days Since Generation')
                            ->state(fn ($record) => $record->created_at->diffInDays(now()) . ' days ago'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
}