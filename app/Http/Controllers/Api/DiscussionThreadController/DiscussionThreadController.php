<?php

namespace App\Http\Controllers\Api\DiscussionThreadController;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\DiscussionThread;
use App\Models\DiscussionReply;
use App\Services\DiscussionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscussionThreadController extends Controller
{
    public function __construct(
        protected DiscussionService $discussionService
    ) {}

    /**
     * عرض جميع النقاشات في الدورة
     * GET /api/learner/courses/{course}/discussions
     */
    public function index(Course $course): JsonResponse
    {
        // التحقق من تسجيل الطالب في الدورة
        if (!$course->students()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مسجل في هذه الدورة'
            ], 403);
        }

        $threads = DiscussionThread::where('course_id', $course->id)
            ->withCount(['replies', 'likes'])
            ->with(['user:id,name,avatar'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $threads
        ]);
    }

    /**
     * عرض نقاش واحد مع الردود
     * GET /api/learner/discussions/{thread}
     */
    public function show(DiscussionThread $thread): JsonResponse
    {
        $user = auth()->user();

        // التحقق من تسجيل الطالب في الدورة
        if (!$thread->course->students()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مسجل في هذه الدورة'
            ], 403);
        }

        $thread->load([
            'user:id,name,avatar',
            'closedBy:id,name',
            'replies' => function ($query) {
                $query->whereNull('parent_id')
                    ->with([
                        'user:id,name,avatar',
                        'replies.user:id,name,avatar',
                        'replies.likes'
                    ])
                    ->withCount(['likes', 'replies'])
                    ->orderBy('created_at', 'asc');
            }
        ]);

        // إضافة معلومات الإعجاب للمستخدم الحالي
        $thread->is_liked_by_user = $thread->isLikedBy($user);
        $thread->likes_count = $thread->likesCount();

        foreach ($thread->replies as $reply) {
            $reply->is_liked_by_user = $reply->isLikedBy($user);
            
            foreach ($reply->replies as $subReply) {
                $subReply->is_liked_by_user = $subReply->isLikedBy($user);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $thread
        ]);
    }
}

class DiscussionReplyController extends Controller
{
    public function __construct(
        protected DiscussionService $discussionService
    ) {}

    /**
     * إضافة رد جديد
     * POST /api/learner/discussions/{thread}/replies
     */
    public function store(Request $request, DiscussionThread $thread): JsonResponse
    {
        $user = auth()->user();

        // التحقق من تسجيل الطالب في الدورة
        if (!$thread->course->students()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مسجل في هذه الدورة'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:5|max:5000',
            'parent_id' => 'nullable|exists:discussion_replies,id'
        ]);

        try {
            $reply = $this->discussionService->createReply(
                $thread,
                $user,
                $validated['content'],
                $validated['parent_id'] ?? null
            );

            $reply->load(['user:id,name,avatar']);
            $reply->is_liked_by_user = false;
            $reply->likes_count = 0;

            return response()->json([
                'success' => true,
                'message' => $reply->is_moderated 
                    ? 'تم إضافة ردك وسيتم مراجعته من قبل المشرف'
                    : 'تم إضافة الرد بنجاح',
                'data' => $reply
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * تعديل رد
     * PUT /api/learner/replies/{reply}
     */
    public function update(Request $request, DiscussionReply $reply): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'content' => 'required|string|min:5|max:5000'
        ]);

        try {
            $updatedReply = $this->discussionService->updateReply(
                $reply,
                $user,
                $validated['content']
            );

            $updatedReply->load(['user:id,name,avatar']);

            return response()->json([
                'success' => true,
                'message' => $updatedReply->is_moderated
                    ? 'تم تعديل ردك وسيتم مراجعته من قبل المشرف'
                    : 'تم تعديل الرد بنجاح',
                'data' => $updatedReply
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * حذف رد
     * DELETE /api/learner/replies/{reply}
     */
    public function destroy(DiscussionReply $reply): JsonResponse
    {
        $user = auth()->user();

        try {
            $this->discussionService->deleteReply($reply, $user);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الرد بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * الإعجاب/إلغاء الإعجاب بالرد
     * POST /api/learner/replies/{reply}/like
     */
    public function toggleLike(DiscussionReply $reply): JsonResponse
    {
        $user = auth()->user();

        // التحقق من تسجيل الطالب في الدورة
        if (!$reply->thread->course->students()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مسجل في هذه الدورة'
            ], 403);
        }

        $liked = $this->discussionService->toggleLike($reply, $user);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $reply->likesCount()
        ]);
    }
}

class DiscussionNotificationController extends Controller
{
    /**
     * عرض إشعارات المستخدم
     * GET /api/learner/discussion-notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = auth()->user()
            ->discussionNotifications()
            ->with(['thread:id,title', 'reply:id,content'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * عرض الإشعارات غير المقروءة فقط
     * GET /api/learner/discussion-notifications/unread
     */
    public function unread(): JsonResponse
    {
        $notifications = auth()->user()
            ->discussionNotifications()
            ->unread()
            ->with(['thread:id,title', 'reply:id,content'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * تعليم الإشعار كمقروء
     * PUT /api/learner/discussion-notifications/{notification}/read
     */
    public function markAsRead($id): JsonResponse
    {
        $notification = auth()->user()
            ->discussionNotifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم الإشعار كمقروء'
        ]);
    }

    /**
     * تعليم جميع الإشعارات كمقروءة
     * PUT /api/learner/discussion-notifications/read-all
     */
    public function markAllAsRead(): JsonResponse
    {
        auth()->user()
            ->discussionNotifications()
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم جميع الإشعارات كمقروءة'
        ]);
    }
}