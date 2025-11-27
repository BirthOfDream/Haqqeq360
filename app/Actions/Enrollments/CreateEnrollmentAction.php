<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Bootcamp;
use Illuminate\Support\Facades\DB;

class CreateEnrollmentAction
{
    public function execute(int $userId, string $enrollableType, int $enrollableId)
    {
        // Determine the model based on type
        $model = $enrollableType === 'course' ? Course::class : Bootcamp::class;
        
        // Find the enrollable item
        $enrollable = $model::withCount('enrollments')->find($enrollableId);
        
        if (!$enrollable) {
            return [
                'success' => false,
                'message' => ucfirst($enrollableType) . ' not found',
                'code' => 'NOT_FOUND'
            ];
        }

        // Check if seats are available
        $availableSeats = max(0, $enrollable->seats - $enrollable->enrollments_count);
        
        if ($availableSeats <= 0) {
            return [
                'success' => false,
                'message' => 'No seats available. This ' . $enrollableType . ' is fully booked.',
                'code' => 'FULLY_BOOKED',
                'data' => [
                    'total_seats' => $enrollable->seats,
                    'enrolled' => $enrollable->enrollments_count,
                    'available' => 0
                ]
            ];
        }

        // Check if user is already enrolled
        $existingEnrollment = Enrollment::where('user_id', $userId)
            ->where('enrollable_type', $model)
            ->where('enrollable_id', $enrollableId)
            ->first();

        if ($existingEnrollment) {
            return [
                'success' => false,
                'message' => 'You are already enrolled in this ' . $enrollableType,
                'code' => 'ALREADY_ENROLLED'
            ];
        }

        // Use transaction to prevent race conditions
        DB::beginTransaction();
        
        try {
            // Double-check seats availability within transaction
            $enrollable->refresh();
            $currentEnrollments = $enrollable->enrollments()->count();
            
            if ($currentEnrollments >= $enrollable->seats) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No seats available. This ' . $enrollableType . ' is fully booked.',
                    'code' => 'FULLY_BOOKED'
                ];
            }

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $userId,
                'enrollable_type' => $model,
                'enrollable_id' => $enrollableId,
                'enrolled_at' => now(),
                'status' => 'active'
            ]);

            DB::commit();

            // Get updated seat information
            $enrollable->refresh();
            $enrollable->loadCount('enrollments');
            $remainingSeats = max(0, $enrollable->seats - $enrollable->enrollments_count);

            return [
                'success' => true,
                'message' => 'Successfully enrolled in ' . $enrollableType,
                'data' => [
                    'enrollment' => $enrollment,
                    'seats_info' => [
                        'total_seats' => $enrollable->seats,
                        'enrolled' => $enrollable->enrollments_count,
                        'available' => $remainingSeats,
                        'is_fully_booked' => $remainingSeats === 0
                    ]
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create enrollment: ' . $e->getMessage(),
                'code' => 'ENROLLMENT_FAILED'
            ];
        }
    }
}