<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Actions\Discussion\CreateDiscussionAction;
use App\Filament\Resources\DiscussionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDiscussion extends CreateRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CreateDiscussionAction::class)->execute($data);
    }
}
