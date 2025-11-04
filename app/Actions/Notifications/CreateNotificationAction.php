<?php

namespace App\Actions\Notifications;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Course;
use App\Models\Bootcamp;
use App\Models\Enrollment;
use App\Jobs\SendBulkNotificationsJob;
use Illuminate\Auth\Access\AuthorizationException;

class CreateNotificationAction
{
    protected NotificationRepositoryInterface $repo;

    public function __construct(NotificationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * $payload structure accepted:
     * - type: 'system'|'course'|'assignment' (required)
     * - title, message (required)
     * - target => one of:
     *     - ['mode' => 'all']
     *     - ['mode' => 'users', 'user_ids' => [...]]
     *     - ['mode' => 'course', 'course_id' => id]
     *     - ['mode' => 'bootcamp', 'bootcamp_id' => id]
     */
    public function execute(array $payload)
    {
        $user = Auth::user();

        $validator = Validator::make($payload, [
            'type' => 'required|in:system,course,assignment',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target.mode' => 'required|string|in:all,users,course,bootcamp',
            'target.user_ids' => 'sometimes|required_if:target.mode,users|array',
            'target.user_ids.*' => 'integer|exists:users,id',
            'target.course_id' => 'sometimes|required_if:target.mode,course|integer|exists:courses,id',
            'target.bootcamp_id' => 'sometimes|required_if:target.mode,bootcamp|integer|exists:bootcamps,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $target = $payload['target'];

        // =========================
        // 🔹 ADMIN ACTIONS
        // =========================
        if ($user->hasRole('admin')) {
            if ($target['mode'] === 'all') {
                SendBulkNotificationsJob::dispatch(
                    $payload['title'],
                    $payload['message'],
                    $payload['type'],
                    null // send to all users
                );
                return ['queued' => true, 'message' => 'Bulk notification dispatched to all users.'];
            }

            if ($target['mode'] === 'users') {
                SendBulkNotificationsJob::dispatch(
                    $payload['title'],
                    $payload['message'],
                    $payload['type'],
                    $target['user_ids']
                );
                return [
                    'queued' => true,
                    'count' => count($target['user_ids']),
                    'message' => 'Notifications queued for specific users.'
                ];
            }
        }

        // =========================
        // 🔹 INSTRUCTOR ACTIONS
        // =========================
        if ($user->hasRole('instructor')) {
            if ($target['mode'] === 'course') {
                $course = Course::find($target['course_id']);
                if (!$course) {
                    throw ValidationException::withMessages(['course' => 'Course not found.']);
                }
                if ($course->user_id !== $user->id) {
                    throw new AuthorizationException('You do not own this course.');
                }

                $studentIds = Enrollment::where('enrollable_type', Course::class)
                    ->where('enrollable_id', $course->id)
                    ->pluck('user_id')
                    ->toArray();

                if (empty($studentIds)) {
                    return ['queued' => false, 'message' => 'No enrolled students found.'];
                }

                SendBulkNotificationsJob::dispatch(
                    $payload['title'],
                    $payload['message'],
                    $payload['type'],
                    $studentIds
                );

                return ['queued' => true, 'count' => count($studentIds), 'message' => 'Notifications queued for course students.'];
            }

            if ($target['mode'] === 'bootcamp') {
                $bootcamp = Bootcamp::find($target['bootcamp_id']);
                if (!$bootcamp) {
                    throw ValidationException::withMessages(['bootcamp' => 'Bootcamp not found.']);
                }
                if ($bootcamp->user_id !== $user->id) {
                    throw new AuthorizationException('You do not own this bootcamp.');
                }

                $studentIds = Enrollment::where('enrollable_type', Bootcamp::class)
                    ->where('enrollable_id', $bootcamp->id)
                    ->pluck('user_id')
                    ->toArray();

                if (empty($studentIds)) {
                    return ['queued' => false, 'message' => 'No enrolled students found.'];
                }

                SendBulkNotificationsJob::dispatch(
                    $payload['title'],
                    $payload['message'],
                    $payload['type'],
                    $studentIds
                );

                return ['queued' => true, 'count' => count($studentIds), 'message' => 'Notifications queued for bootcamp students.'];
            }
        }

        // =========================
        // 🔹 DEFAULT (UNAUTHORIZED)
        // =========================
        throw new AuthorizationException('You are not allowed to send notifications in this manner.');
    }
}
