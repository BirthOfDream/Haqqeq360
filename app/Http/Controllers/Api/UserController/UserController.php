<?php

namespace App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Users\{
    CreateUserAction,
    ShowUserAction,
    UpdateUserAction,
    DeleteUserAction
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    protected CreateUserAction $createUser;
    protected ShowUserAction $showUser;
    protected UpdateUserAction $updateUser;
    protected DeleteUserAction $deleteUser;

    public function __construct(
        CreateUserAction $createUser,
        ShowUserAction $showUser,
        UpdateUserAction $updateUser,
        DeleteUserAction $deleteUser
    ) {
        $this->middleware('auth:sanctum');
        $this->createUser = $createUser;
        $this->showUser = $showUser;
        $this->updateUser = $updateUser;
        $this->deleteUser = $deleteUser;
    }

    public function index()
    {
        $users = app(\App\Repositories\User\UserRepository::class)->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can create users.'
                ], 403);
            }

            $result = $this->createUser->execute($request->all());
            $status = $result['success'] ? 201 : 400;
            return response()->json($result, $status);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $result = $this->showUser->execute($id);
        $status = $result['success'] ? 200 : 404;
        return response()->json($result, $status);
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->updateUser->execute($id, $request->all());
            $status = $result['success'] ? 200 : 404;
            return response()->json($result, $status);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $result = $this->deleteUser->execute($id);
        $status = $result['success'] ? 200 : 404;
        return response()->json($result, $status);
    }
}
