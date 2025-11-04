<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Enrollment updated')
            ->body('The enrollment has been updated successfully.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If status is changed to completed, set progress to 100
        if ($data['status'] === 'completed' && $data['progress'] < 100) {
            $data['progress'] = 100;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Notify student of status change
        // If completed, trigger certificate generation
        // Update related reports
    }
}
