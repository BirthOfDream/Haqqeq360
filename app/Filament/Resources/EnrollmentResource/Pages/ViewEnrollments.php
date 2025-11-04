<?php 


namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnrollment extends ViewRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('generateReport')
                ->label('Generate Report')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    // Logic to generate report for this enrollment
                    \App\Models\Report::updateOrCreate(
                        [
                            'user_id' => $this->record->user_id,
                            'enrollable_type' => $this->record->enrollable_type,
                            'enrollable_id' => $this->record->enrollable_id,
                        ],
                        [
                            'completion_rate' => $this->record->progress,
                            'grade_avg' => null, // Calculate from assessments
                            'feedback_summary' => 'Auto-generated report',
                        ]
                    );

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Report Generated')
                        ->body('The report has been generated successfully.')
                        ->send();
                }),
        ];
    }
}