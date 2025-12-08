<?php

// app/Http/Controllers/DiscussionController.php

namespace App\Http\Controllers\Api\DiscussionController;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Like\ToggleLikeAction;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Repositories\DiscussionRepository\DiscussionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DiscussionController extends Controller
{
    public function __construct(
        private DiscussionRepository $discussionRepository,
        private CreateCommentAction $createCommentAction,
        private ToggleLikeAction $toggleLikeAction
    ) {}

    public function index(): JsonResponse
    {
        $discussions = $this->discussionRepository->getPublishedDiscussions();

        return response()->json([
            'success' => true,
            'data' => $discussions,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $discussion = $this->discussionRepository->findById($id);

        if (!$discussion || (!$discussion->is_published && $discussion->user_id !== Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Discussion not found',
            ], 404);
        }

        $comments = $this->discussionRepository->getCommentsByDiscussion($id);

        return response()->json([
            'success' => true,
            'data' => [
                'discussion' => $discussion,
                'comments' => $comments,
            ],
        ]);
    }

    public function comment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:discussion_comments,id',
        ]);

        $discussion = $this->discussionRepository->findById($id);

        if (!$discussion || !$discussion->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Discussion not found',
            ], 404);
        }

        $comment = $this->createCommentAction->execute([
            'discussion_id' => $id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'data' => $comment->load('user'),
            'message' => 'Comment added successfully',
        ], 201);
    }

    public function likeDiscussion(int $id): JsonResponse
    {
        $discussion = $this->discussionRepository->findById($id);

        if (!$discussion) {
            return response()->json([
                'success' => false,
                'message' => 'Discussion not found',
            ], 404);
        }

        $liked = $this->toggleLikeAction->execute($discussion, Auth::id());

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => $liked,
                'likes_count' => $discussion->likes()->count(),
            ],
            'message' => $liked ? 'Discussion liked' : 'Discussion unliked',
        ]);
    }

    public function likeComment(int $commentId): JsonResponse
    {
        $comment = DiscussionComment::find($commentId);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        $liked = $this->toggleLikeAction->execute($comment, Auth::id());

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => $liked,
                'likes_count' => $comment->likes()->count(),
            ],
            'message' => $liked ? 'Comment liked' : 'Comment unliked',
        ]);
    }
}