<?php

namespace App\Filament\Resources\BootcampResource\Pages;

use App\Filament\Resources\BootcampResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBootcamp extends CreateRecord
{
    protected static string $resource = BootcampResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Bootcamp created')
            ->body('The bootcamp has been created successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // You can modify data before creating if needed
        return $data;
    }

    protected function afterCreate(): void
    {
        // You can perform actions after creation
        // For example: send notification to instructor
    }
}
