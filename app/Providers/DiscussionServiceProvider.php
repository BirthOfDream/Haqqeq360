<?php

namespace App\Providers;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\DeleteCommentAction;
use App\Actions\Discussion\CreateDiscussionAction;
use App\Actions\Discussion\DeleteDiscussionAction;
use App\Actions\Discussion\UpdateDiscussionAction;
use App\Actions\Like\ToggleLikeAction;
use App\Repositories\DiscussionRepository\CommentRepository;
use App\Repositories\DiscussionRepository\DiscussionRepository;
use App\Repositories\DiscussionRepository\LikeRepository;
use Illuminate\Support\ServiceProvider;

class DiscussionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->singleton(DiscussionRepository::class, function ($app) {
            return new DiscussionRepository();
        });

        $this->app->singleton(CommentRepository::class, function ($app) {
            return new CommentRepository();
        });

        $this->app->singleton(LikeRepository::class, function ($app) {
            return new LikeRepository();
        });

        // Register Actions
        $this->app->singleton(CreateDiscussionAction::class, function ($app) {
            return new CreateDiscussionAction($app->make(DiscussionRepository::class));
        });

        $this->app->singleton(UpdateDiscussionAction::class, function ($app) {
            return new UpdateDiscussionAction($app->make(DiscussionRepository::class));
        });

        $this->app->singleton(DeleteDiscussionAction::class, function ($app) {
            return new DeleteDiscussionAction($app->make(DiscussionRepository::class));
        });

        $this->app->singleton(CreateCommentAction::class, function ($app) {
            return new CreateCommentAction($app->make(CommentRepository::class));
        });

        $this->app->singleton(DeleteCommentAction::class, function ($app) {
            return new DeleteCommentAction($app->make(CommentRepository::class));
        });

        $this->app->singleton(ToggleLikeAction::class, function ($app) {
            return new ToggleLikeAction($app->make(LikeRepository::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}