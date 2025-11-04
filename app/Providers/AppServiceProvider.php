<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\BootcampRepositoryInterface;
use App\Repositories\Bootcamp\BootcampRepository;
use App\Repositories\Course\CourseRepository;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the interface to the concrete implementation
        $this->app->bind(
            BootcampRepositoryInterface::class,
            BootcampRepository::class
        );
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
         $this->app->bind(
        \App\Repositories\Interfaces\ProgramRepositoryInterface::class,
        \App\Repositories\Program\ProgramRepository::class
    );

    }

    public function boot(): void
    {
        //
    }
}