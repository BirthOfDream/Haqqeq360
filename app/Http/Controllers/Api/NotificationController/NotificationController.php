<?php

namespace App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

use App\Actions\Notifications\{
    IndexNotificationAction,
    ShowNotificationAction,
    CreateNotificationAction,
    ToggleReadNotificationAction,
    DeleteNotificationAction
};

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    protected function success($data, $message = 'Success', $status = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error($message, $status = 400)
    {
        // if $message is array (validation errors), return it under "errors"
        if (is_array($message)) {
            return response()->json(['success' => false, 'errors' => $message], $status);
        }
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    public function index(IndexNotificationAction $action)
    {
        try {
            $data = $action->execute();
            return $this->success($data, 'Notifications retrieved');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show($id, ShowNotificationAction $action)
    {
        try {
            $notification = $action->execute((int)$id);
            return $this->success($notification, 'Notification retrieved');
        } catch (ModelNotFoundException $e) {
            return $this->error('Notification not found', 404);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(Request $request, CreateNotificationAction $action)
    {
        try {
            $result = $action->execute($request->all());
            // result is a summary array for bulk operations
            return $this->success($result, 'Notifications processed', 201);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), 422);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function markRead($id, ToggleReadNotificationAction $action)
    {
        try {
            $notification = $action->markRead((int)$id);
            return $this->success($notification, 'Notification marked as read');
        } catch (ModelNotFoundException $e) {
            return $this->error('Notification not found', 404);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function markUnread($id, ToggleReadNotificationAction $action)
    {
        try {
            $notification = $action->markUnread((int)$id);
            return $this->success($notification, 'Notification marked as unread');
        } catch (ModelNotFoundException $e) {
            return $this->error('Notification not found', 404);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy($id, DeleteNotificationAction $action)
    {
        try {
            $action->execute((int)$id);
            return $this->success(null, 'Notification deleted');
        } catch (ModelNotFoundException $e) {
            return $this->error('Notification not found', 404);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
