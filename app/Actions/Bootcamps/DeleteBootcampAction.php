<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;
use App\Repositories\Interfaces\BootcampRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteBootcampAction
{
    protected $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Bootcamp $bootcamp): bool
    {
        $user = Auth::user();

        if ($user->id !== $bootcamp->user_id && !$user->hasRole('admin')) {
            throw ValidationException::withMessages(['permission' => 'You are not allowed to delete this bootcamp.']);
        }

        return $this->repo->delete($bootcamp);
    }
}
