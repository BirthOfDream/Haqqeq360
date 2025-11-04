<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Report created')
            ->body('The report has been created successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-fetch completion rate from enrollment if exists
        $enrollment = \App\Models\Enrollment::where('user_id', $data['user_id'])
            ->where('enrollable_id', $data['enrollable_id'])
            ->where('enrollable_type', $data['enrollable_type'])
            ->first();

        if ($enrollment) {
            $data['completion_rate'] = $enrollment->progress;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send report notification to student
        // Send report notification to instructor
        // Log report generation
    }
}