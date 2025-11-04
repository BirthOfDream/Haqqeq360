<?php

namespace App\Actions\Users;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class CreateUserAction
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $user = $this->userRepository->create($data);

            return [
                'success' => true,
                'message' => 'User created successfully.',
                'data' => $user,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create user.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
