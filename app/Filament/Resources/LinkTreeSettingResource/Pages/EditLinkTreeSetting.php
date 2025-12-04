<?php

namespace App\Filament\Resources\LinkTreeSettingResource\Pages;

use App\Filament\Resources\LinkTreeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLinkTreeSetting extends EditRecord
{
    protected static string $resource = LinkTreeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
