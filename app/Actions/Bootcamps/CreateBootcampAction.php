<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;
use App\Repositories\Interfaces\BootcampRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateBootcampAction
{
    protected $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(array $data): Bootcamp
    {
        $user = Auth::user();

        if (!$user->hasRole('instructor')) {
            throw ValidationException::withMessages(['role' => 'Only instructors can create bootcamps.']);
        }

        $validated = validator($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_weeks' => 'required|integer|min:1',
        ])->validate();

        $validated['user_id'] = $user->id;

        return $this->repo->create($validated);
    }
}
