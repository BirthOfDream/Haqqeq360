<?php

namespace App\Http\Controllers\Api\SubmissionController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Actions\Submissions\{
    CreateSubmissionAction,
    UpdateSubmissionAction,
    DeleteSubmissionAction,
    ShowSubmissionAction
};
use App\Repositories\SubmissionRepositoryInterface;
use App\Models\Submission;
use Exception;

class SubmissionController extends Controller
{
    protected $repo;

    public function __construct(SubmissionRepositoryInterface $repo)
    {
        $this->middleware('auth:sanctum');
        $this->repo = $repo;
    }

    public function store(Request $request, CreateSubmissionAction $action)
    {
        try {
            $submission = $action->execute($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Submission created successfully.',
                'data' => $submission
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id, ShowSubmissionAction $action)
    {
        try {
            $submission = $action->execute($id);
            return response()->json(['status' => 'success', 'data' => $submission]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Submission not found.'], 404);
        }
    }

    public function update(Request $request, $id, UpdateSubmissionAction $action)
    {
        try {
            $submission = $this->repo->findById($id);
            if (!$submission) {
                throw new ModelNotFoundException();
            }

            $updated = $action->execute($submission, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Submission updated successfully.',
                'data' => $updated
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Submission not found.'], 404);
        }
    }

    public function destroy($id, DeleteSubmissionAction $action)
    {
        try {
            $submission = $this->repo->findById($id);
            if (!$submission) {
                throw new ModelNotFoundException();
            }

            $action->execute($submission);
            return response()->json([
                'status' => 'success',
                'message' => 'Submission deleted successfully.'
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Submission not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
