<?php


// app/Filament/Resources/DiscussionResource/Pages/EditDiscussion.php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Actions\Discussion\UpdateDiscussionAction;
use App\Filament\Resources\DiscussionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscussion extends EditRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        app(UpdateDiscussionAction::class)->execute($record, $data);
        return $record->fresh();
    }
}
