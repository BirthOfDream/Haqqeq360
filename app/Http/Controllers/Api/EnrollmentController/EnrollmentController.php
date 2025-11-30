<?php

namespace App\Http\Controllers\Api\EnrollmentController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

use App\Actions\Enrollments\{
    CreateEnrollmentAction,
    UpdateEnrollmentAction,
    DeleteEnrollmentAction,
    ShowEnrollmentAction
};

use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        // Middleware will be applied in routes file
    }

    protected function success($data, $message = 'Success', $code = 200)
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    protected function error($message, $code = 400)
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
    }

    public function index()
    {
        try {
            $enrollments = Enrollment::with(['user', 'enrollable'])
                ->where('user_id', Auth::id())
                ->get();
            return $this->success($enrollments, 'Enrollments retrieved successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show($id, ShowEnrollmentAction $action)
    {
        try {
            $enrollment = $action->execute($id);
            return $this->success($enrollment, 'Enrollment details fetched');
        } catch (ModelNotFoundException $e) {
            return $this->error('Enrollment not found', 404);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(Request $request, CreateEnrollmentAction $action)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'enrollable_type' => 'required|string|in:course,bootcamp,workshop,program',
                'enrollable_id' => 'required|integer|min:1'
            ]);

            // Get authenticated user
            $userId = Auth::id();

            // Execute enrollment action
            $result = $action->execute(
                $userId,
                $validated['enrollable_type'],
                $validated['enrollable_id']
            );

            // Return appropriate response based on result
            if ($result['success']) {
                return $this->success($result['data'], $result['message'], 201);
            } else {
                $statusCode = match($result['code']) {
                    'NOT_FOUND' => 404,
                    'FULLY_BOOKED' => 409,
                    'ALREADY_ENROLLED' => 409,
                    'INVALID_TYPE' => 422,
                    default => 400
                };
                
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'code' => $result['code'],
                    'data' => $result['data'] ?? null
                ], $statusCode);
            }

        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update($id, Request $request, UpdateEnrollmentAction $action)
    {
        try {
            $enrollment = Enrollment::findOrFail($id);
            
            // Check if user owns this enrollment
            if ($enrollment->user_id !== Auth::id()) {
                return $this->error('Unauthorized', 403);
            }
            
            $updated = $action->execute($enrollment, $request->all());
            return $this->success($updated, 'Enrollment updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('Enrollment not found', 404);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy($id, DeleteEnrollmentAction $action)
    {
        try {
            $enrollment = Enrollment::findOrFail($id);
            
            // Check if user owns this enrollment
            if ($enrollment->user_id !== Auth::id()) {
                return $this->error('Unauthorized', 403);
            }
            
            $action->execute($enrollment);
            return $this->success(null, 'Enrollment deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('Enrollment not found', 404);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}