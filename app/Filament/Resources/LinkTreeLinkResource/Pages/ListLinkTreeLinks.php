<?php

namespace App\Filament\Resources\LinkTreeLinkResource\Pages;

use App\Filament\Resources\LinkTreeLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinkTreeLinks extends ListRecords
{
    protected static string $resource = LinkTreeLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة رابط جديد'),
            Actions\Action::make('view_page')
                ->label('معاينة الصفحة')
                ->icon('heroicon-o-eye')
                ->url(fn () => url('/links'))
                ->openUrlInNewTab()
                ->color('gray'),
        ];
    }
}
