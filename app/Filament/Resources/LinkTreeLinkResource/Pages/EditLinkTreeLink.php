<?php

namespace App\Filament\Resources\LinkTreeLinkResource\Pages;

use App\Filament\Resources\LinkTreeLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLinkTreeLink extends EditRecord
{
    protected static string $resource = LinkTreeLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الرابط بنجاح';
    }
}