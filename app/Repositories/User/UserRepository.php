<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements UserRepositoryInterface
{
    public function getAll()
    {
        return User::with(['courses', 'bootcamps', 'enrollments', 'notifications'])->get();
    }

    public function findById(int $id)
    {
        $user = User::with(['courses', 'bootcamps', 'enrollments', 'notifications'])->find($id);
        if (!$user) {
            throw new ModelNotFoundException("User with ID {$id} not found.");
        }
        return $user;
    }

    public function create(array $data)
    {
        $data['password'] = bcrypt($data['password']);
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException("User with ID {$id} not found.");
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function delete(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            throw new ModelNotFoundException("User with ID {$id} not found.");
        }

        $user->delete();
        return true;
    }
}
