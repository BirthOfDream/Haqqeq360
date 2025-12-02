<?php

namespace App\Http\Controllers\Api\EvaluationController;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EvaluationResponseController extends Controller
{
    /**
     * Get available evaluations for student
     */
    public function getAvailableEvaluations(Request $request)
    {
        $user = $request->user();
        
        // Get student's enrolled products
        // This depends on your enrollment structure
        $evaluations = Evaluation::active()
            ->whereDoesntHave('responses', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereNotNull('completed_at');
            })
            ->with('questions')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $evaluations
        ]);
    }

    /**
     * Submit evaluation response
     */
    public function submit(Request $request, Evaluation $evaluation)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:evaluation_questions,id',
            'answers.*.answer_value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already completed this evaluation
        $existingResponse = EvaluationResponse::where('evaluation_id', $evaluation->id)
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();

        if ($existingResponse) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بالفعل بإكمال هذا التقييم'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $response = EvaluationResponse::firstOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'user_id' => $user->id,
                ],
                [
                    'completed_at' => null
                ]
            );

            // Delete old answers if exists
            $response->answers()->delete();

            // Save new answers
            foreach ($request->answers as $answer) {
                $response->answers()->create([
                    'question_id' => $answer['question_id'],
                    'answer_value' => $answer['answer_value'],
                ]);
            }

            // Mark as completed
            $response->markAsCompleted();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التقييم بنجاح',
                'data' => $response->load('answers.question')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال التقييم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluation results/statistics
     */
    public function getResults(Evaluation $evaluation)
    {
        $responses = EvaluationResponse::where('evaluation_id', $evaluation->id)
            ->completed()
            ->with(['answers.question', 'user'])
            ->get();

        $statistics = [];
        
        foreach ($evaluation->questions as $question) {
            $answers = $responses->pluck('answers')
                ->flatten()
                ->where('question_id', $question->id);

            $stats = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'total_responses' => $answers->count(),
            ];

            if (in_array($question->question_type, ['scale', 'grade', 'yes_no'])) {
                $stats['distribution'] = $answers->groupBy('answer_value')
                    ->map(fn($items) => $items->count())
                    ->toArray();
            }

            if ($question->question_type === 'rating') {
                $values = $answers->pluck('answer_value')->map(fn($v) => (float) $v);
                $stats['average'] = $values->average();
                $stats['min'] = $values->min();
                $stats['max'] = $values->max();
            }

            if ($question->question_type === 'text') {
                $stats['responses'] = $answers->pluck('answer_value')->toArray();
            }

            $statistics[] = $stats;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'evaluation' => $evaluation,
                'total_responses' => $responses->count(),
                'statistics' => $statistics,
            ]
        ]);
    }
}