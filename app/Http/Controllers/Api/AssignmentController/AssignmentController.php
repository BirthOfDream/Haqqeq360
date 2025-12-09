<?php

namespace App\Http\Controllers\Api\AssignmentController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;


use App\Actions\Assignments\{
    CreateAssignmentAction,
    UpdateAssignmentAction,
    DeleteAssignmentAction,
    ShowAssignmentAction
};

use App\Repositories\Interfaces\AssignmentRepositoryInterface;

class AssignmentController extends Controller
{
    protected $repo;

    public function __construct(AssignmentRepositoryInterface $repo)
    {
        $this->middleware('auth:sanctum');
        $this->repo = $repo;
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
            $data = $this->repo->getAll();
            return $this->success($data, 'Assignments retrieved successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show($id, ShowAssignmentAction $action)
    {
        try {
            $assignment = $action->execute($id);
            return $this->success($assignment, 'Assignment details fetched');
        } catch (ModelNotFoundException $e) {
            return $this->error('Assignment not found', 404);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(Request $request, CreateAssignmentAction $action)
    {
        try {
            $assignment = $action->execute($request->all());
            return $this->success($assignment, 'Assignment created successfully', 201);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update($id, Request $request, UpdateAssignmentAction $action)
    {
        try {
            $assignment = $this->repo->findById($id);
            $updated = $action->execute($assignment, $request->all());
            return $this->success($updated, 'Assignment updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('Assignment not found', 404);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy($id, DeleteAssignmentAction $action)
    {
        try {
            $assignment = $this->repo->findById($id);
            $action->execute($assignment);
            return $this->success(null, 'Assignment deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('Assignment not found', 404);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
