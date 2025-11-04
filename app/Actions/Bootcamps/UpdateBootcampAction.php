<?php

namespace App\Actions\Bootcamps;

use App\Models\Bootcamp;
use App\Repositories\Interfaces\BootcampRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateBootcampAction
{
    protected $repo;

    public function __construct(BootcampRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Bootcamp $bootcamp, array $data): Bootcamp
    {
        $user = Auth::user();

        if ($user->id !== $bootcamp->user_id) {
            throw ValidationException::withMessages(['permission' => 'You can only update your own bootcamps.']);
        }

        $validated = validator($data, [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'duration_weeks' => 'sometimes|integer|min:1',
        ])->validate();

        return $this->repo->update($bootcamp, $validated);
    }
}
