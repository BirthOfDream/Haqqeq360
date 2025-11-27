<?php

namespace App\Filament\Resources\BootCampResource\Pages;

use App\Filament\Resources\BootCampResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBootCamp extends EditRecord
{
    protected static string $resource = BootCampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
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
            ->title('Bootcamp updated')
            ->body('The bootcamp has been updated successfully.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // You can modify data before saving if needed
        return $data;
    }

    protected function afterSave(): void
    {
        // You can perform actions after saving
        // For example: notify enrolled students of changes
    }
}
