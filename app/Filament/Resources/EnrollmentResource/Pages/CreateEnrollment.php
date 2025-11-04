<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Enrollment created')
            ->body('The student has been enrolled successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if enrollment already exists
        $exists = \App\Models\Enrollment::where('user_id', $data['user_id'])
            ->where('enrollable_id', $data['enrollable_id'])
            ->where('enrollable_type', $data['enrollable_type'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->warning()
                ->title('Duplicate Enrollment')
                ->body('This student is already enrolled in this program.')
                ->send();
            
            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notification to student about enrollment
        // Send notification to instructor
        // Log the enrollment activity
    }
}