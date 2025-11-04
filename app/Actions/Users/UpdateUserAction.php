<?php

namespace App\Actions\Users;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class UpdateUserAction
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $id, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $user = $this->userRepository->update($id, $data);
            return [
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => $user,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
