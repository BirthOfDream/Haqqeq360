<?php

namespace App\Http\Controllers\Api\AssignmentController;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
     * Get assignments by lesson (course/unit/lesson hierarchy)
     */
    public function getByLesson(int $courseId, int $unitId, int $lessonId): JsonResponse
    {
        $assignments = Assignment::with(['course', 'unit', 'lesson'])
            ->where('course_id', $courseId)
            ->where('unit_id', $unitId)
            ->where('lesson_id', $lessonId)
            ->where('published', true)
            ->orderBy('due_date', 'asc')
            ->get();

        $userId = auth()->id();
        $submissions = Submission::where('user_id', $userId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return response()->json([
            'success' => true,
            'data' => $assignments->map(function ($assignment) use ($submissions) {
                $submission = $submissions->get($assignment->id);
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'max_score' => $assignment->max_score,
                    'attachment_url' => $assignment->attachment_path 
                        ? Storage::url($assignment->attachment_path) 
                        : null,
                    'is_overdue' => $assignment->due_date && now()->isAfter($assignment->due_date),
                    'days_until_due' => $assignment->due_date 
                        ? now()->diffInDays($assignment->due_date, false) 
                        : null,
                    'user_submission' => $submission ? [
                        'id' => $submission->id,
                        'file_url' => Storage::url($submission->file_url),
                        'grade' => $submission->grade,
                        'submitted_at' => $submission->submitted_at,
                        'is_graded' => $submission->grade !== null,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Get a specific assignment
     */
    public function show(int $courseId, int $unitId, int $lessonId, int $id): JsonResponse
    {
        $assignment = Assignment::with(['course', 'unit', 'lesson'])
            ->where('id', $id)
            ->where('course_id', $courseId)
            ->where('unit_id', $unitId)
            ->where('lesson_id', $lessonId)
            ->where('published', true)
            ->firstOrFail();

        $userId = auth()->id();
        $submission = Submission::where('assignment_id', $id)
            ->where('user_id', $userId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'due_date' => $assignment->due_date,
                'max_score' => $assignment->max_score,
                'attachment_url' => $assignment->attachment_path 
                    ? Storage::url($assignment->attachment_path) 
                    : null,
                'course' => $assignment->course,
                'unit' => $assignment->unit,
                'lesson' => $assignment->lesson,
                'is_overdue' => $assignment->due_date && now()->isAfter($assignment->due_date),
                'days_until_due' => $assignment->due_date 
                    ? now()->diffInDays($assignment->due_date, false) 
                    : null,
                'user_submission' => $submission ? [
                    'id' => $submission->id,
                    'file_url' => Storage::url($submission->file_url),
                    'grade' => $submission->grade,
                    'submitted_at' => $submission->submitted_at,
                    'is_graded' => $submission->grade !== null,
                ] : null,
            ],
        ]);
    }

    /**
     * Get assignments valid for a specific date
     * GET /api/assignments/by-date?date=2024-12-15
     */
    public function getByValidityDate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = Carbon::parse($request->date);
        $userId = auth()->id();

        // Get assignments where the due date is on or after the provided date
        // and the assignment is published
        $assignments = Assignment::with(['course', 'unit', 'lesson'])
            ->where('published', true)
            ->where(function($query) use ($date) {
                $query->whereNull('due_date')
                      ->orWhere('due_date', '>=', $date);
            })
            ->orderBy('due_date', 'asc')
            ->get();

        $submissions = Submission::where('user_id', $userId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return response()->json([
            'success' => true,
            'query_date' => $date->toDateString(),
            'data' => $assignments->map(function ($assignment) use ($submissions) {
                $submission = $submissions->get($assignment->id);
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'max_score' => $assignment->max_score,
                    'course' => [
                        'id' => $assignment->course->id,
                        'name' => $assignment->course->name ?? $assignment->course->title,
                    ],
                    'unit' => [
                        'id' => $assignment->unit->id,
                        'name' => $assignment->unit->name ?? $assignment->unit->title,
                    ],
                    'lesson' => [
                        'id' => $assignment->lesson->id,
                        'name' => $assignment->lesson->name ?? $assignment->lesson->title,
                    ],
                    'attachment_url' => $assignment->attachment_path 
                        ? Storage::url($assignment->attachment_path) 
                        : null,
                    'is_overdue' => $assignment->due_date && now()->isAfter($assignment->due_date),
                    'days_until_due' => $assignment->due_date 
                        ? now()->diffInDays($assignment->due_date, false) 
                        : null,
                    'user_submission' => $submission ? [
                        'id' => $submission->id,
                        'file_url' => Storage::url($submission->file_url),
                        'grade' => $submission->grade,
                        'submitted_at' => $submission->submitted_at,
                        'is_graded' => $submission->grade !== null,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Get assignments in progress with points summary
     * GET /api/assignments/in-progress
     */
    public function getInProgress(): JsonResponse
    {
        $userId = auth()->id();
        $now = now();
        $sixMonthsAgo = $now->copy()->subMonths(6);
        $oneYearAgo = $now->copy()->subYear();

        // Get assignments that are:
        // 1. Published
        // 2. Either have no due date OR due date is in the future
        // 3. User has NOT submitted OR submitted but not graded yet
        $assignments = Assignment::with(['course', 'unit', 'lesson'])
            ->where('published', true)
            ->where(function($query) use ($now) {
                $query->whereNull('due_date')
                      ->orWhere('due_date', '>=', $now);
            })
            ->whereHas('submissions', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->whereNull('grade');
            }, '<=', 1)
            ->whereDoesntHave('submissions', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->whereNotNull('grade');
            })
            ->orderBy('due_date', 'asc')
            ->get();

        // Get user submissions for these assignments
        $submissions = Submission::where('user_id', $userId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        // Calculate points for last 6 months
        $pointsLast6Months = Submission::where('user_id', $userId)
            ->whereNotNull('grade')
            ->where('submitted_at', '>=', $sixMonthsAgo)
            ->sum('grade');

        // Calculate points for last year
        $pointsLastYear = Submission::where('user_id', $userId)
            ->whereNotNull('grade')
            ->where('submitted_at', '>=', $oneYearAgo)
            ->sum('grade');

        // Get detailed breakdown by month for last 6 months
        $monthlyBreakdown = Submission::where('user_id', $userId)
            ->whereNotNull('grade')
            ->where('submitted_at', '>=', $sixMonthsAgo)
            ->select(
                DB::raw('DATE_FORMAT(submitted_at, "%Y-%m") as month'),
                DB::raw('SUM(grade) as total_points'),
                DB::raw('COUNT(*) as assignments_completed')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'points_summary' => [
                'last_6_months' => (float) $pointsLast6Months,
                'last_year' => (float) $pointsLastYear,
                'monthly_breakdown' => $monthlyBreakdown->map(function($item) {
                    return [
                        'month' => $item->month,
                        'points' => (float) $item->total_points,
                        'assignments_completed' => $item->assignments_completed,
                    ];
                }),
            ],
            'in_progress_count' => $assignments->count(),
            'data' => $assignments->map(function ($assignment) use ($submissions) {
                $submission = $submissions->get($assignment->id);
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'max_score' => $assignment->max_score,
                    'course' => [
                        'id' => $assignment->course->id,
                        'name' => $assignment->course->name ?? $assignment->course->title,
                    ],
                    'unit' => [
                        'id' => $assignment->unit->id,
                        'name' => $assignment->unit->name ?? $assignment->unit->title,
                    ],
                    'lesson' => [
                        'id' => $assignment->lesson->id,
                        'name' => $assignment->lesson->name ?? $assignment->lesson->title,
                    ],
                    'attachment_url' => $assignment->attachment_path 
                        ? Storage::url($assignment->attachment_path) 
                        : null,
                    'is_overdue' => $assignment->due_date && now()->isAfter($assignment->due_date),
                    'days_until_due' => $assignment->due_date 
                        ? now()->diffInDays($assignment->due_date, false) 
                        : null,
                    'status' => $submission ? 'submitted_ungraded' : 'not_submitted',
                    'user_submission' => $submission ? [
                        'id' => $submission->id,
                        'file_url' => Storage::url($submission->file_url),
                        'submitted_at' => $submission->submitted_at,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Submit an assignment
     */
    public function submit(Request $request, int $courseId, int $unitId, int $lessonId, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $assignment = Assignment::where('id', $id)
            ->where('course_id', $courseId)
            ->where('unit_id', $unitId)
            ->where('lesson_id', $lessonId)
            ->where('published', true)
            ->firstOrFail();

        $userId = auth()->id();

        // Check if already submitted
        $existingSubmission = Submission::where('assignment_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted this assignment.',
            ], 422);
        }

        // Upload file
        $file = $request->file('file');
        $path = $file->store('submissions/' . $id, 'public');

        // Create submission
        $submission = Submission::create([
            'assignment_id' => $id,
            'user_id' => $userId,
            'file_url' => $path,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment submitted successfully.',
            'data' => [
                'id' => $submission->id,
                'file_url' => Storage::url($submission->file_url),
                'submitted_at' => $submission->submitted_at,
            ],
        ], 201);
    }

    /**
     * Update submission (resubmit)
     */
    public function resubmit(Request $request, int $courseId, int $unitId, int $lessonId, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $assignment = Assignment::where('id', $id)
            ->where('course_id', $courseId)
            ->where('unit_id', $unitId)
            ->where('lesson_id', $lessonId)
            ->where('published', true)
            ->firstOrFail();

        $userId = auth()->id();
        $submission = Submission::where('assignment_id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Delete old file
        if (Storage::disk('public')->exists($submission->file_url)) {
            Storage::disk('public')->delete($submission->file_url);
        }

        // Upload new file
        $file = $request->file('file');
        $path = $file->store('submissions/' . $id, 'public');

        // Update submission
        $submission->update([
            'file_url' => $path,
            'submitted_at' => now(),
            'grade' => null, // Reset grade on resubmission
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment resubmitted successfully.',
            'data' => [
                'id' => $submission->id,
                'file_url' => Storage::url($submission->file_url),
                'submitted_at' => $submission->submitted_at,
            ],
        ]);
    }
}