<?php

namespace App\Filament\Resources\LinkTreeSettingResource\Pages;

use App\Filament\Resources\LinkTreeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinkTreeSettings extends ListRecords
{
    protected static string $resource = LinkTreeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
