<?php

namespace App\Filament\Resources\CommonQuestionsResource\Pages;

use App\Filament\Resources\CommonQuestionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommonQuestions extends EditRecord
{
    protected static string $resource = CommonQuestionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
