<?php

namespace App\Http\Controllers\Api\EnrollmentController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

use App\Actions\Enrollment\{
    CreateEnrollmentAction,
    UpdateEnrollmentAction,
    DeleteEnrollmentAction,
    ShowEnrollmentAction
};

use App\Repositories\Contracts\EnrollmentRepositoryInterface;

class EnrollmentController extends Controller
{
    protected $repo;

    public function __construct(EnrollmentRepositoryInterface $repo)
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
            return $this->success($data, 'Enrollments retrieved successfully');
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
            $enrollment = $action->execute($request->all());
            return $this->success($enrollment, 'Enrollment created successfully', 201);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update($id, Request $request, UpdateEnrollmentAction $action)
    {
        try {
            $enrollment = $this->repo->findById($id);
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
            $enrollment = $this->repo->findById($id);
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
