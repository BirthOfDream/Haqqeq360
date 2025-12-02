<?php

namespace App\Filament\Resources\EvaluationResource\Pages;

use App\Filament\Resources\EvaluationResource;
use App\Models\Evaluation;
use Filament\Resources\Pages\Page;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewEvaluationResults extends Page
{
    protected static string $resource = EvaluationResource::class;

    protected static string $view = 'filament.resources.evaluation-resource.pages.view-evaluation-results';

    public Evaluation $record;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export')
                ->label('تصدير النتائج')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // Export logic here
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'نتائج التقييم: ' . $this->record->product_name;
    }

    public function getViewData(): array
    {
        $responses = $this->record->responses()
            ->with(['answers.question', 'user'])
            ->whereNotNull('completed_at')
            ->get();

        $statistics = [];
        
        foreach ($this->record->questions as $question) {
            $answers = $responses->pluck('answers')
                ->flatten()
                ->where('question_id', $question->id);

            $stats = [
                'question' => $question,
                'total_responses' => $answers->count(),
            ];

            if (in_array($question->question_type, ['scale', 'grade', 'yes_no'])) {
                $distribution = $answers->groupBy('answer_value')
                    ->map(fn($items) => $items->count())
                    ->toArray();
                
                $stats['distribution'] = $distribution;
                $stats['chart_data'] = [
                    'labels' => array_keys($distribution),
                    'data' => array_values($distribution),
                ];
            }

            if ($question->question_type === 'rating') {
                $values = $answers->pluck('answer_value')->map(fn($v) => (float) $v);
                $stats['average'] = round($values->average(), 2);
                $stats['min'] = $values->min();
                $stats['max'] = $values->max();
            }

            if ($question->question_type === 'text') {
                $stats['text_responses'] = $answers->pluck('answer_value')->toArray();
            }

            $statistics[] = $stats;
        }

        return [
            'evaluation' => $this->record,
            'total_responses' => $responses->count(),
            'statistics' => $statistics,
        ];
    }
}