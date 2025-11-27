<?php

namespace App\Filament\Resources\CommonQuestionsResource\Pages;

use App\Filament\Resources\CommonQuestionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommonQuestions extends ListRecords
{
    protected static string $resource = CommonQuestionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
