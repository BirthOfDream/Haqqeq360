<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\BootcampRepositoryInterface;
use App\Repositories\Bootcamp\BootcampRepository;
use App\Repositories\Course\CourseRepository;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Enrollment\EnrollmentRepository;
use App\Repositories\Interfaces\ProgramRepositoryInterface;
use App\Repositories\Program\ProgramRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Bootcamp Repository
        $this->app->bind(
            BootcampRepositoryInterface::class,
            BootcampRepository::class
        );

        // Bind Course Repository
        $this->app->bind(
            CourseRepositoryInterface::class,
            CourseRepository::class
        );

        // Bind Program Repository
        $this->app->bind(
            ProgramRepositoryInterface::class,
            ProgramRepository::class
        );

        // Bind Enrollment Repository
        $this->app->bind(
            EnrollmentRepositoryInterface::class,
            EnrollmentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}