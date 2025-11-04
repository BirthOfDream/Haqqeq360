<?php

namespace App\Actions\Users;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DeleteUserAction
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $id)
    {
        try {
            $this->userRepository->delete($id);
            return [
                'success' => true,
                'message' => 'User deleted successfully.',
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
