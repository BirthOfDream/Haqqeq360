<?php

namespace App\Actions\Users;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ShowUserAction
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $id)
    {
        try {
            $user = $this->userRepository->findById($id);
            return [
                'success' => true,
                'message' => 'User retrieved successfully.',
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
                'message' => 'Error retrieving user.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
