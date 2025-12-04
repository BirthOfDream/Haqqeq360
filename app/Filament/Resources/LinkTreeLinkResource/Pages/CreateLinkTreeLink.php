<?php

namespace App\Filament\Resources\LinkTreeLinkResource\Pages;

use App\Filament\Resources\LinkTreeLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLinkTreeLink extends CreateRecord
{
    protected static string $resource = LinkTreeLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة الرابط بنجاح';
    }
}
